<?php

namespace App\Infrastructure\Integrations\Hosting\InMemory;

use App\Domain\Sender\Contracts\SenderService;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;

class InMemoryHostingService implements SenderService
{
    public function send(UploadedFileInterface $fileToUpload): HostedFileData
    {
        return new HostedFileData(
            fileId: 1,
            fileName: 'inMemory.png',
            webViewLink: 'http://localhost:8080/inMemory.png',
            webContentLink: 'http://localhost:8080/inMemory.png'
        );
    }
}
