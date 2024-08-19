<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\HostingData;

interface HostingRepository
{
    /**
     * @param string[] $hostingSlugs
     * @return HostingData[]
     */
    public function queryBySlugs(array $hostingSlugs): array;
}
