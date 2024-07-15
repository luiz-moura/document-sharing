<?php

declare(strict_types=1);

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\Contracts\SenderService;
use App\Infrastructure\Integrations\Hosting\InMemory\InMemoryHostingService;
use App\Infrastructure\Persistence\InMemory\InMemoryFileHostingRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryFileRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryHostingRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        FileRepository::class => \DI\autowire(InMemoryFileRepository::class),
        FileHostingRepository::class => \DI\autowire(InMemoryFileHostingRepository::class),
        HostingRepository::class => \DI\autowire(InMemoryHostingRepository::class),
        SenderService::class => \DI\autowire(InMemoryHostingService::class),
    ]);
};
