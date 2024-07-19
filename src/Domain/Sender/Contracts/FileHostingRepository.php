<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\UpdateAcessLinkFileHostingData;

interface FileHostingRepository
{
    public function create(CreateFileHostingData $fileHosting): int;
    public function updateAcessLink(int $fileHostingId, UpdateAcessLinkFileHostingData $fileHosting): void;
}
