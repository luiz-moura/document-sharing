<?php

declare(strict_types=1);

namespace App\Domain\Sender\Entities;

class HostingEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $refreshableToken,
        public readonly ?string $accessToken,
    ) {
    }
}
