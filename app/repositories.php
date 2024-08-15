<?php

declare(strict_types=1);

use App\Domain\Sender\Contracts\FileHostingRepository as FileHostingRepositoryContract;
use App\Domain\Sender\Contracts\FileRepository as FileRepositoryContract;
use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Infrastructure\Integrations\Hosting\FileSenderFactory as HostingFileSenderFactory;
use App\Infrastructure\Persistence\Doctrine\Entities\FileEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\HostedFileEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\HostingEntity;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        /** Factories */
        FileSenderFactoryContract::class => \DI\autowire(HostingFileSenderFactory::class),

        /** Repositories */
        FileRepositoryContract::class => function (ContainerInterface $container) {
            return $container->get(EntityManagerInterface::class)->getRepository(FileEntity::class);
        },
        HostingRepositoryContract::class => function (ContainerInterface $container) {
            return $container->get(EntityManagerInterface::class)->getRepository(HostingEntity::class);
        },
        FileHostingRepositoryContract::class => function (ContainerInterface $container) {
            return $container->get(EntityManagerInterface::class)->getRepository(HostedFileEntity::class);
        },
    ]);
};
