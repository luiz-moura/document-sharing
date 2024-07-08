<?php

declare(strict_types=1);

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Files\InMemoryFileHostingRepository;
use App\Infrastructure\Persistence\Files\InMemoryFileRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
        FileRepository::class => \DI\autowire(InMemoryFileRepository::class),
        FileHostingRepository::class => \DI\autowire(InMemoryFileHostingRepository::class),
    ]);
};
