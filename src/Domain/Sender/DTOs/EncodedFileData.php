<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class EncodedFileData
{
    public function __construct(
        public readonly string $base64,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
    ) {
    }
}
