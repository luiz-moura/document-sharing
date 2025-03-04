<?php

namespace App\Infrastructure\Adapters\Uuid;

use App\Domain\Common\Adapters\Contracts\UuidGeneratorService as UuidGeneratorServiceContract;
use Ramsey\Uuid\Uuid;

class UuidGeneratorService implements UuidGeneratorServiceContract
{
    public function __construct(private Uuid $uuid)
    {
    }

    public function generateUuid(): string
    {
        return $this->uuid->uuid4()->toString();
    }
}
