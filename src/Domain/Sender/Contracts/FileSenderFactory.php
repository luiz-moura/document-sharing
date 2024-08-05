<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

interface FileSenderFactory
{
    public function create(string $type): FileSenderService;
}
