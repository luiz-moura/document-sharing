<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\FileRepository as FileRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileData;

class FileRepository implements FileRepositoryContract
{
    public function create(CreateFileData $file): int
    {
        return 1;
    }
}
