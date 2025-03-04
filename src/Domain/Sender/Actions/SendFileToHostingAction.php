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
        private readonly HostedFileRepository $fileHostingRepository,
        private readonly FileSenderFactory $fileSenderFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendFileToHostingData $sendFileToHosting): void
    {
        $this->fileHostingRepository->updateStatus(
            $sendFileToHosting->hostedFileId,
            FileStatusOnHostEnum::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hostingSlug
        );

        try {
            $hostedFile = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (Throwable $exception) {
            $this->fail($exception, $sendFileToHosting);
        }

        $this->fileHostingRepository->updateAccessLink(
            $sendFileToHosting->hostedFileId,
            new UpdateAccessLinkHostedFileData(
                externalId: $hostedFile->fileId,
                webViewLink: $hostedFile->webViewLink,
                webContentLink: $hostedFile->webContentLink,
            )
        );
    }

    private function fail(Throwable $exception, SendFileToHostingData $sendFileToHosting): never
    {
        $this->logger->error(sprintf('[%s] Failed to send file to service', __METHOD__), [
            'hosting_slug' => $sendFileToHosting->hostingSlug,
            'hosted_file_id' => $sendFileToHosting->hostedFileId,
            'filename' => $sendFileToHosting->encodedFile->filename,
            'mime_type' => $sendFileToHosting->encodedFile->mimeType,
            'size' => $sendFileToHosting->encodedFile->size,
            'exception' => $exception,
        ]);

        $this->fileHostingRepository->updateStatus(
            $sendFileToHosting->hostedFileId,
            FileStatusOnHostEnum::SEND_FAILURE
        );

        throw new FailedToUploadFileToHostingException(previous: $exception);
    }
}
