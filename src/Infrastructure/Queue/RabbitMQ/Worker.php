<?php

declare(strict_types=1);

require __DIR__ . '/../../../../app/bootstrap.php';

use App\Infrastructure\Queue\Contracts\QueueManagerInterface;
use Faker\Container\ContainerInterface;

/** @var ContainerInterface $container */
/** @var QueueManagerInterface $queueManager */
$queueManager = $container->get(QueueManagerInterface::class);

pcntl_signal(SIGINT, function () use ($queueManager): never {
    echo 'Gracefully stopping...' . PHP_EOL;

    $queueManager->disconnect();

    exit(0);
});

$queueManager->consume('app');
