<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusEnum;
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
            FileStatusEnum::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hosting->slug
        );

        try {
            $hostedFile = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (Throwable $ex) {
            $this->logsFileUploadError($ex, $sendFileToHosting);

            $this->hostedFileRepository->updateStatus(
                $sendFileToHosting->hostedFileId,
                FileStatusEnum::SEND_FAILURE
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

    private function logsFileUploadError(Throwable $ex, SendFileToHostingData $sendFileToHosting): void
    {
        $this->logger->error(
            sprintf('[%s] Failed to send file to service %s', __METHOD__, $sendFileToHosting->hosting->slug),
            context: [
                'exception' => (string) $ex,
                'file_hosting' => [
                    'hosted_file_id' => $sendFileToHosting->hostedFileId,
                    'file_name' => $sendFileToHosting->encodedFile->filename,
                    'media_type' => $sendFileToHosting->encodedFile->mediaType,
                    'size' => $sendFileToHosting->encodedFile->size,
                ],
            ]
        );
    }
}
