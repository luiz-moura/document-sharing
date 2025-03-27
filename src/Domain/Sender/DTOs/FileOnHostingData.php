<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class FileOnHostingData
{
    public function __construct(
        public readonly string $fileId,
        public readonly string $filename,
        public readonly string $webViewLink,
        public readonly string $webContentLink,
    ) {
    }
}
