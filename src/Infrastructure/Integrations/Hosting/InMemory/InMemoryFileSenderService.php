<?php

namespace App\Infrastructure\Integrations\Hosting\InMemory;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;

class InMemoryFileSenderService implements FileSenderService
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