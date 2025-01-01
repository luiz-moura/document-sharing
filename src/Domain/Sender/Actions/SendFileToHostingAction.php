<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusOnHostEnum;
use App\Domain\Sender\Exceptions\FailedToUploadFileToHostingException;
use Psr\Log\LoggerInterface;
use Throwable;

class SendFileToHostingAction
{
    public function __construct(
        private readonly HostedFileRepository $hostedFileRepository,
        private readonly FileSenderFactory $fileSenderFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendFileToHostingData $sendFileToHosting): void
    {
        $this->hostedFileRepository->updateStatus(
            $sendFileToHosting->hostedFileId,
            FileStatusOnHostEnum::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hostingSlug
        );

        try {
            $hostedFile = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[%s] Failed to send file to service', __METHOD__), [
                'exception' => (string) $exception,
                'hosting_slug' => $sendFileToHosting->hostingSlug,
                'hosted_file_id' => $sendFileToHosting->hostedFileId,
                'file' => [
                    'filename' => $sendFileToHosting->encodedFile->filename,
                    'media_type' => $sendFileToHosting->encodedFile->mediaType,
                    'size' => $sendFileToHosting->encodedFile->size,
                ]
            ]);

            $this->hostedFileRepository->updateStatus(
                $sendFileToHosting->hostedFileId,
                FileStatusOnHostEnum::SEND_FAILURE
            );

            throw new FailedToUploadFileToHostingException(previous: $exception);
        }

        $this->hostedFileRepository->updateAccessLink(
            $sendFileToHosting->hostedFileId,
            new UpdateAccessLinkHostedFileData(
                externalFileId: $hostedFile->fileId,
                webViewLink: $hostedFile->webViewLink,
                webContentLink: $hostedFile->webContentLink,
            )
        );
    }
}
