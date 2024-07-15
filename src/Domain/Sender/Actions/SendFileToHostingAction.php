<?php

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\SenderService;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;

class SendFileToHostingAction
{
    public function __construct(
        private SenderService $senderService,
    ) {}

    public function __invoke(UploadedFileInterface $uploadedFile, int $hostId): HostedFileData
    {
        return $this->senderService->send($uploadedFile);
    }
}
