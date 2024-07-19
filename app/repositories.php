<?php

declare(strict_types=1);

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Infrastructure\Integrations\Hosting\FileSenderFactory as HostingFileSenderFactory;
use App\Infrastructure\Persistence\InMemory\InMemoryFileHostingRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryFileRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryHostingRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        FileRepository::class => \DI\autowire(InMemoryFileRepository::class),
        FileHostingRepository::class => \DI\autowire(InMemoryFileHostingRepository::class),
        HostingRepository::class => \DI\autowire(InMemoryHostingRepository::class),
        FileSenderFactory::class => \DI\autowire(HostingFileSenderFactory::class),
    ]);
};
