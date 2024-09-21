<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting\InMemory;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostedFileData;

class InMemoryFileSenderService implements FileSenderService
{
    public function send(EncodedFileData $encodedFile): HostedFileData
    {
        return new HostedFileData(
            fileId: '312312da',
            fileName: $encodedFile->filename,
            webViewLink: "http://localhost:8080/{$encodedFile->filename}.{$encodedFile->mediaType}",
            webContentLink: "http://localhost:8080/{$encodedFile->filename}.{$encodedFile->mediaType}"
        );
    }
}
