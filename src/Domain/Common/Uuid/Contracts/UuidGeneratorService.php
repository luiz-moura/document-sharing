<?php

namespace App\Domain\Common\Uuid\Contracts;

interface UuidGeneratorService
{
    public function generateUuid(): string;
}
