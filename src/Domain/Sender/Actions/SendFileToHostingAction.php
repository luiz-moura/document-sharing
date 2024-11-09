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
        private HostedFileRepository $hostedFileRepository,
        private FileSenderFactory $fileSenderFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendFileToHostingData $sendFileToHosting): void
    {
        $this->hostedFileRepository->updateStatus(
            $sendFileToHosting->hostedFileId,
            FileStatusOnHostEnum::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hosting->slug
        );

        try {
            $hostedFile = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (Throwable $ex) {
            $this->logErrorSendingFile($ex, $sendFileToHosting);

            $this->hostedFileRepository->updateStatus(
                $sendFileToHosting->hostedFileId,
                FileStatusOnHostEnum::SEND_FAILURE
            );

            throw new FailedToUploadFileToHostingException(previous: $ex);
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

    private function logErrorSendingFile(Throwable $ex, SendFileToHostingData $sendFileToHosting): void
    {
        $this->logger->error(sprintf('[%s] Failed to send file to service', __METHOD__), [
            'exception' => (string) $ex,
            'hosting_slug' => $sendFileToHosting->hosting->slug,
            'hosted_file_id' => $sendFileToHosting->hostedFileId,
            'file_name' => $sendFileToHosting->encodedFile->filename,
            'media_type' => $sendFileToHosting->encodedFile->mediaType,
            'size' => $sendFileToHosting->encodedFile->size,
        ]);
    }
}
