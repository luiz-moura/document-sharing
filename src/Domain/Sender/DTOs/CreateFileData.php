<?php

namespace App\Domain\Sender\DTOs;

class CreateFileData
{
    public function __construct(
        private string $name,
        private float $size,
        private string $type,
    ) {}
}
