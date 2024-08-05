<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\HostingData;

class InMemoryHostingRepository implements HostingRepository
{
    public function queryByIds(array $ids): array
    {
        return [
            new HostingData(
                id: 1,
                name: 'dropbox'
            )
        ];
    }
}
