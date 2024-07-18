<?php

namespace App\Domain\Sender\DTOs;

class CreateFileData
{
    public function __construct(
        public string $name,
        public float $size,
        public string $mimeType,
    ) {}
}
