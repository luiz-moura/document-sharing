<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class HostingData
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public ?string $refreshableToken = null,
        public ?string $accessToken = null,
    ) {
    }
}
