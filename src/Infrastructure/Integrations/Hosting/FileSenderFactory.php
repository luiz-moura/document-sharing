<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting;

use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Infrastructure\Integrations\Hosting\Dropbox\DropboxService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryFileSenderService;
use App\Infrastructure\Integrations\Hosting\Common\Enums\HostingEnum;
use App\Infrastructure\Integrations\Hosting\Common\Exceptions\FileSenderHostingNotFoundException;
use Psr\Container\ContainerInterface;

class FileSenderFactory implements FileSenderFactoryContract
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * @throws FileSenderHostingNotFoundException
     */
    public function create(string $type): FileSenderService
    {
        $hosting = HostingEnum::tryFrom($type);

        return match ($hosting) {
            HostingEnum::IN_MEMORY => new InMemoryFileSenderService(),
            HostingEnum::DROPBOX => $this->container->get(DropboxService::class),
            default => throw new FileSenderHostingNotFoundException($type)
        };
    }
}
