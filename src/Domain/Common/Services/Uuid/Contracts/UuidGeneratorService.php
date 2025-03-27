<?php

namespace App\Domain\Common\Services\Uuid\Contracts;

interface UuidGeneratorService
{
    public function generateUuid(): string;
}
