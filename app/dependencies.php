<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Domain\Common\Queue\Contracts\Publisher;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Queue\Contracts\QueueManagerInterface;
use App\Infrastructure\Queue\RabbitMQ\QueueManager;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c): LoggerInterface {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManagerInterface::class => function (): EntityManagerInterface {
            $em = require __DIR__ . '/../src/Infrastructure/Persistence/Doctrine/entity-manager.php';

            return $em;
        },
        QueueManagerInterface::class => function (ContainerInterface $c): QueueManagerInterface {
            return new QueueManager(
                maxRetries: 1,
                retryDelaySeconds: 1,
                logger: $c->get(LoggerInterface::class),
                container: $c,
            );
        },
        Publisher::class => function (ContainerInterface $c): QueueManagerInterface {
            return $c->get(QueueManagerInterface::class);
        },
        CacheInterface::class => DI\factory(function (): Psr16Cache {
            $filesystemAdapter = new FilesystemAdapter(
                namespace: 'app_cache',
                directory: __DIR__ . '/../var/cache'
            );

            return new Psr16Cache($filesystemAdapter);
        }),
    ]);
};
