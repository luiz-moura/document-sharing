<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;

class InMemoryFileHostingRepository implements FileHostingRepository
{
    public function create(CreateFileHostingData $fileHosting): int
    {
        return 1;
    }

    public function updateAcessLink(int $fileHostingId, \App\Domain\Sender\DTOs\UpdateAcessLinkFileHostingData $fileHosting): void
    {
        sleep(3);
    }
}
