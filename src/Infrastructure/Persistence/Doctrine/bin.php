<?php

require __DIR__ . '/../../../../app/bootstrap.php';

use App\Application\Settings\SettingsInterface;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\Migrations\DependencyFactory;

/** @var SettingsInterface $settings */

/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = require __DIR__ . '/entity-manager.php';

$config = new ConfigurationArray($settings->get('database.doctrine'));
$dependencyFactory = DependencyFactory::fromEntityManager(
    $config,
    new ExistingEntityManager($entityManager)
);

$commands = [
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
    new Command\DiffCommand($dependencyFactory),
    new Command\UpToDateCommand($dependencyFactory),
];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);
