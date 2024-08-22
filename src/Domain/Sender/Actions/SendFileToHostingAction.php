<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Exceptions\FailedToUploadFileToHostingException;
use Psr\Log\LoggerInterface;
use Throwable;

class SendFileToHostingAction
{
    public function __construct(
        private HostedFileRepository $hostedFileRepository,
        private FileSenderFactory $fileSenderFactory,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SendFileToHostingData $sendFileToHosting): void {
        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hosting->slug
        );

        try {
            $hostedFile = $fileSenderService->send($sendFileToHosting->uploadedFile);
        } catch (Throwable $ex) {
            $this->logsFileUploadError($ex, $sendFileToHosting);

            throw new FailedToUploadFileToHostingException();
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

    private function logsFileUploadError(Throwable $ex, SendFileToHostingData $sendFileToHosting) {
        $this->logger->error(
            sprintf('Failed to send file to service %s', $sendFileToHosting->hosting->slug),
            context: [
                'exception' => (string) $ex,
                'hosting_id' => $sendFileToHosting->hosting->id,
                'file_id' => $sendFileToHosting->hostedFileId,
                'uploadedFile' => [
                    'filename' => $sendFileToHosting->uploadedFile->getClientFilename(),
                    'media_type' => $sendFileToHosting->uploadedFile->getClientMediaType(),
                    'size' => $sendFileToHosting->uploadedFile->getSize(),
                ],
            ]
        );
    }
}
