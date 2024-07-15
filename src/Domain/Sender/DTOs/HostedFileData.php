<?php

namespace App\Domain\Sender\DTOs;

class HostedFileData
{
    public function __construct(
        private int $fileId,
        private string $fileName,
        private string $webViewLink,
        private string $webContentLink,
    ) {}
}
