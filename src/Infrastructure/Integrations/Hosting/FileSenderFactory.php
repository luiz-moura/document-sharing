<?php

namespace App\Infrastructure\Integrations\Hosting;

use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryFileSenderService;
use Exception;

class FileSenderFactory implements FileSenderFactoryContract
{
    public function create(string $type): FileSenderService {
        return match ($type) {
            'in Memory' => new InMemoryFileSenderService(),
            default => throw new Exception('Wrong file sender type passed.')
        };
    }
}
