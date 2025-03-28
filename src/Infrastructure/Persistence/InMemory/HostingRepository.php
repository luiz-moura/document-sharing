<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Domain\Sender\DTOs\HostingEntity as DomainHostingEntity;

class HostingRepository implements HostingRepositoryContract
{
    public function queryBySlugs(array $slugs): array
    {
        $i = 1;

        return array_map(
            fn (string $slug): DomainHostingEntity => new DomainHostingEntity(
                id: $i++,
                slug: $slug,
                name: 'in Memory',
                refreshableToken: 'refreshableToken',
                accessToken: 'accessToken',
            ),
            $slugs
        );
    }

    public function findBySlug(string $slug): ?DomainHostingEntity
    {
        return new DomainHostingEntity(
            id: 1,
            slug: $slug,
            name: 'in Memory',
            refreshableToken: 'refreshableToken',
            accessToken: 'accessToken',
        );
    }

    public function updateRefreshableTokenBySlug(string $slug, string $codeAccess): void
    {
        sleep(1);
    }

    public function updateAccessTokenBySlug(string $slug, string $codeAccess): void
    {
        sleep(1);
    }
}
