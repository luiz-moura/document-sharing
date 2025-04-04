<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAttributes(true);

// Set up env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

if ($_ENV['APP_ENV'] === 'prod') {
    $containerBuilder->enableCompilation(__DIR__ . '/../tmp/container');
}

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();
