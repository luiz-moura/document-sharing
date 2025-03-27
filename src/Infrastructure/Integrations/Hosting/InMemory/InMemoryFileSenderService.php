<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting\InMemory;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\FileOnHostingData;

class InMemoryFileSenderService implements FileSenderService
{
    public function send(EncodedFileData $encodedFile): FileOnHostingData
    {
        return new FileOnHostingData(
            fileId: '312312da',
            filename: $encodedFile->filename,
            webViewLink: sprintf('http://localhost:8080/%s.%s', $encodedFile->filename, $encodedFile->mimeType),
            webContentLink: sprintf('http://localhost:8080/%s.%s', $encodedFile->filename, $encodedFile->mimeType),
        );
    }
}
