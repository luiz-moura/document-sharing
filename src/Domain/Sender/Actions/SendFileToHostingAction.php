<?php

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\UpdateAcessLinkFileHostingData;
use Psr\Http\Message\UploadedFileInterface;

class SendFileToHostingAction
{
    public function __construct(
        private FileSenderFactory $fileSenderFactory,
        private FileHostingRepository $fileHostingRepository
    ) {}

    public function __invoke(
        int $fileHostingId,
        HostingData $hosting,
        UploadedFileInterface $uploadedFile
    ): void {
        $fileSenderService = $this->fileSenderFactory::create($hosting->name);

        $hostedFile =  $fileSenderService->send($uploadedFile);

        $this->fileHostingRepository->updateAcessLink(
            $fileHostingId,
            new UpdateAcessLinkFileHostingData(
                externalFileId: $hostedFile->fileId,
                webViewLink: $hostedFile->webViewLink,
                webContentLink: $hostedFile->webContentLink,
            )
        );
    }
}
