<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class EncodedFileData
{
    public function __construct(
        public readonly string $filename,
        public readonly string $mediaType,
        public readonly int $size,
        public readonly string $base64,
    ) {
    }
}
