<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c): LoggerInterface {
            $settings = $c->get(SettingsInterface::class);

            $logger = new Logger($settings->get('logger.name'));

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($settings->get('logger.path'), $settings->get('logger.level'));
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
            $settings = $c->get(SettingsInterface::class);

            $em = require $settings->get('database.entity_manager_path');

            return $em;
        },
        CacheInterface::class => function (ContainerInterface $c): Psr16Cache {
            $settings = $c->get(SettingsInterface::class);

            $filesystemAdapter = new FilesystemAdapter(
                namespace: 'app_cache',
                directory: $settings->get('tmp_dir') . '/cache',
            );

            return new Psr16Cache($filesystemAdapter);
        },
    ]);
};
