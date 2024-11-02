<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting;

use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Infrastructure\Integrations\Hosting\Dropbox\DropboxService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryFileSenderService;
use App\Infrastructure\Integrations\Hosting\Enums\HostingEnum;
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
        $hosting = HostingEnum::tryFrom($type);

        if (! $hosting) {
            throw new Exception(sprintf('Unsupported file sender type: %s', $type));
        }

        return match ($hosting) {
            HostingEnum::IN_MEMORY => new InMemoryFileSenderService(),
            HostingEnum::DROPBOX => $this->container->get(DropboxService::class),
        };
    }
}
