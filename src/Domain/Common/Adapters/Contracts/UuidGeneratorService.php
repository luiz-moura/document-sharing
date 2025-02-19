<?php

namespace App\Domain\Common\Adapters\Contracts;

interface UuidGeneratorService
{
    public function generateUuid(): string;
}
