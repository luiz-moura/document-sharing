<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting;

use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Infrastructure\Integrations\Hosting\Dropbox\DropboxService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryFileSenderService;
use Exception;
use Psr\Container\ContainerInterface;

class FileSenderFactory implements FileSenderFactoryContract
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function create(string $type): FileSenderService
    {
        return match ($type) {
            'in-memory' => new InMemoryFileSenderService(),
            'dropbox' => $this->container->get(DropboxService::class),
            default => throw new Exception(sprintf('Unsupported file sender type: %s', $type)),
        };
    }
}
