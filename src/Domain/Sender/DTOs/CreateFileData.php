<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class CreateFileData
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly int $size,
        public readonly string $mimeType,
    ) {
    }
}
