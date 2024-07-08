<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Files;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;

class InMemoryFileHostingRepository implements FileHostingRepository
{
    public function create(CreateFileHostingData $fileHosting): int
    {
        return 1;
    }
}
