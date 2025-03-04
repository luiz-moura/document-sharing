<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class CreateFileData
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
    ) {
    }
}
