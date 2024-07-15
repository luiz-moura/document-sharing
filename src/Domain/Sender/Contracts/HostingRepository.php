<?php

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\HostingData;

interface HostingRepository
{
    /**
     * @param int[] $hostingIds
     * @return HostingData[]
     */
    public function queryByIds(array $hostingIds): array;
}
