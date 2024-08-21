<?php

namespace App\Infrastructure\Adapters\Uuid;

use App\Domain\Common\Uuid\Contracts\UuidGeneratorService as UuidGeneratorServiceContract;
use Ramsey\Uuid\Uuid;

class UuidGeneratorService implements UuidGeneratorServiceContract
{
    public function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
