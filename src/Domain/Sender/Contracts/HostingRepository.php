<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\Entities\HostingEntity;

interface HostingRepository
{
    /**
     * @param string[] $hostingSlugs
     *
     * @return HostingEntity[]
     */
    public function queryBySlugs(array $hostingSlugs): array;
    public function findBySlug(string $slug): ?HostingEntity;
    public function updateRefreshableTokenBySlug(string $slug, string $codeAccess): void;
    public function updateAccessTokenBySlug(string $slug, string $accessToken): void;
}
