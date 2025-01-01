<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class HostingData
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $refreshableToken = null,
        public readonly ?string $accessToken = null,
    ) {
    }
}
