<?php

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkFileHostingData;

class SendFileToHostingAction
{
    public function __construct(
        private FileSenderFactory $fileSenderFactory,
        private FileHostingRepository $fileHostingRepository
    ) {}

    public function __invoke(SendFileToHostingData $sendFileToHosting): void {
        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hosting->name
        );

        $hostedFile = $fileSenderService->send($sendFileToHosting->uploadedFile);

        $this->fileHostingRepository->updateAccessLink(
            $sendFileToHosting->fileHostingId,
            new UpdateAccessLinkFileHostingData(
                externalFileId: $hostedFile->fileId,
                webViewLink: $hostedFile->webViewLink,
                webContentLink: $hostedFile->webContentLink,
            )
        );
    }
}
