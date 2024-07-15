<?php

namespace App\Domain\Sender\DTOs;

class HostingData
{
    public function __construct(
        private int $id,
        private string $name,
    ) {}
}
