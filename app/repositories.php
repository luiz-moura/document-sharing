<?php

declare(strict_types=1);

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryFileHostingRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryFileRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        FileRepository::class => \DI\autowire(InMemoryFileRepository::class),
        FileHostingRepository::class => \DI\autowire(InMemoryFileHostingRepository::class),
    ]);
};
