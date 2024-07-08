<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\CreateFileHostingData;

interface FileHostingRepository
{
    public function create(CreateFileHostingData $fileHosting): int;
}
