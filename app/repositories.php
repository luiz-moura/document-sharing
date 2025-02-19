<?php

declare(strict_types=1);

use App\Domain\Common\Adapters\Contracts\UuidGeneratorService as UuidGeneratorServiceContract;
use App\Domain\Sender\Contracts\HostedFileRepository as HostedFileRepositoryContract;
use App\Domain\Sender\Contracts\FileRepository as FileRepositoryContract;
use App\Domain\Sender\Contracts\FileSenderFactory as FileSenderFactoryContract;
use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Infrastructure\Adapters\Uuid\UuidGeneratorService;
use App\Infrastructure\Integrations\Hosting\FileSenderFactory as HostingFileSenderFactory;
use App\Infrastructure\Persistence\Doctrine\Entities\FileEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\HostedFileEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\HostingEntity;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        /** Factories */
        FileSenderFactoryContract::class => \DI\autowire(HostingFileSenderFactory::class),

        /** Repositories */
        FileRepositoryContract::class => function (ContainerInterface $container): FileRepositoryContract {
            return $container->get(EntityManagerInterface::class)->getRepository(FileEntity::class);
        },
        HostingRepositoryContract::class => function (ContainerInterface $container): HostingRepositoryContract {
            return $container->get(EntityManagerInterface::class)->getRepository(HostingEntity::class);
        },
        HostedFileRepositoryContract::class => function (ContainerInterface $container): HostedFileRepositoryContract {
            return $container->get(EntityManagerInterface::class)->getRepository(HostedFileEntity::class);
        },

        /** Services */
        UuidGeneratorServiceContract::class => \DI\autowire(UuidGeneratorService::class),
    ]);
};
