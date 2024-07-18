<?php

namespace App\Domain\Sender\DTOs;

class HostedFileData
{
    public function __construct(
        public int $fileId,
        public string $fileName,
        public string $webViewLink,
        public string $webContentLink,
    ) {}
}
