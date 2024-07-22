<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkFileHostingData;

class InMemoryFileHostingRepository implements FileHostingRepository
{
    public function create(CreateFileHostingData $fileHosting): int
    {
        return 1;
    }

    public function updateAccessLink(int $fileHostingId, UpdateAccessLinkFileHostingData $fileHosting): void
    {
        sleep(3);
    }
}
