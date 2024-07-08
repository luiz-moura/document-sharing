<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\CreateFileData;

interface FileRepository
{
    public function create(CreateFileData $file): int;
}
