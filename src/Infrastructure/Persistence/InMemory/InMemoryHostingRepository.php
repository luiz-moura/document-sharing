<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\HostingData;

class InMemoryHostingRepository implements HostingRepository
{
    public function queryBySlugs(array $slugs): array
    {
        $i = 1;

        return array_map(
            fn (string $slug) => new HostingData(
                id: $i++,
                name: 'in Memory',
                slug: $slug,
            )
        , $slugs);
    }
}
