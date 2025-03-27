<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\HostingData;

interface HostingRepository
{
    /**
     * @param string[] $hostingSlugs
     *
     * @return HostingData[]
     */
    public function queryBySlugs(array $hostingSlugs): array;
    public function findBySlug(string $slug): ?HostingData;
    public function updateRefreshableTokenBySlug(string $slug, string $codeAccess): void;
    public function updateAccessTokenBySlug(string $slug, string $accessToken): void;
}
