<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class EncodedFileData
{
    public function __construct(
        public string $filename,
        public string $mediaType,
        public int $size,
        public string $base64,
    ) {
    }
}
