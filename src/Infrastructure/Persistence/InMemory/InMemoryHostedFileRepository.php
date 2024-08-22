<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;

class InMemoryHostedFileRepository implements HostedFileRepository
{
    public function create(CreateHostedFileData $hostedFile): int
    {
        return 1;
    }

    public function updateAccessLink(int $hostedFileId, UpdateAccessLinkHostedFileData $hostedFile): void
    {
        sleep(3);
    }
}
