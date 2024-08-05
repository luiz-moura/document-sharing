<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class HostedFileData
{
    public function __construct(
        public string $fileId,
        public string $fileName,
        public string $webViewLink,
        public string $webContentLink,
    ) {}
}
