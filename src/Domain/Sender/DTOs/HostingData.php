<?php

namespace App\Domain\Sender\DTOs;

class HostingData
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
