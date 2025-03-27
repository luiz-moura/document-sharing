<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class CreateFileHostingData
{
    public function __construct(
        public readonly int $fileId,
        public readonly int $hostingId,
    ) {
    }
}
