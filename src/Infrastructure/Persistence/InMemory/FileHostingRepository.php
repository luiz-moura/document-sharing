<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\FileHostingRepository as FileHostingRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\Enums\FileHostingStatus;

class FileHostingRepository implements FileHostingRepositoryContract
{
    public function create(CreateFileHostingData $fileHosting): int
    {
        sleep(1);

        return 1;
    }

    public function updateStatus(int $fileHostingId, FileHostingStatus $status): void
    {
        sleep(1);
    }
}
