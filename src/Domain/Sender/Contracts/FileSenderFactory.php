<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\Exceptions\InvalidFileSenderException;

interface FileSenderFactory
{
    /**
     * @throws InvalidFileSenderException
     */
    public function create(string $type): FileSenderService;
}
