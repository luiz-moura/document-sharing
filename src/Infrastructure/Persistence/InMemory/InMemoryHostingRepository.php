<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\HostingRepository;

class InMemoryHostingRepository implements HostingRepository
{
    public function queryByIds(array $ids): array
    {
        return [];
    }
}
