<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting;

use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Infrastructure\Integrations\Hosting\Dropbox\DropboxService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryFileSenderService;
use Exception;

class FileSenderFactory implements FileSenderFactoryContract
{
    public function create(string $type): FileSenderService {
        return match ($type) {
            'in-memory' => new InMemoryFileSenderService(),
            'dropbox' => new DropboxService(),
            default => throw new Exception('Wrong file sender type passed.')
        };
    }
}
