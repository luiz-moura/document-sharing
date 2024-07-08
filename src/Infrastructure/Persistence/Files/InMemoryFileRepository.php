<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Files;

use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\DTOs\CreateFileData;

class InMemoryFileRepository implements FileRepository
{
    public function create(CreateFileData $file): int
    {
        return 1;
    }
}
