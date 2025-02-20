<?php

use App\Application\Settings\SettingsInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;

require __DIR__ . '/../../../../app/bootstrap.php';

/** @var ContainerInterface $container */

/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$ormSetup = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/Entities'],
    isDevMode: $settings->get('app.env') === 'dev',
);

$connection = DriverManager::getConnection($settings->get('database.doctrine'), $ormSetup);

return new EntityManager($connection, $ormSetup);
