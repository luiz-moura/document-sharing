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
            fn (string $slug): HostingData => new HostingData(
                id: $i++,
                slug: $slug,
                name: 'in Memory',
                refreshableToken: 'refreshableToken',
                accessToken: 'accessToken',
            ),
            $slugs
        );
    }

    public function findBySlug(string $slug): ?HostingData
    {
        return new HostingData(
            id: 1,
            slug: $slug,
            name: 'in Memory',
            refreshableToken: 'refreshableToken',
            accessToken: 'accessToken',
        );
    }

    public function updateRefreshableTokenBySlug(string $slug, string $codeAccess): void
    {
        // do nothing
    }

    public function updateAccessTokenBySlug(string $slug, string $codeAccess): void
    {
        // do nothing
    }
}
