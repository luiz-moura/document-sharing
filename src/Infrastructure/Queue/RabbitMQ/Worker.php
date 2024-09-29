<?php

require __DIR__ . '/../../../../app/bootstrap.php';

use App\Infrastructure\Queue\Contracts\QueueManagerInterface;
use Faker\Container\ContainerInterface;

/**
 * @var ContainerInterface $container
 */

/** @var QueueManagerInterface $queueManager */
$queueManager = $container->get(QueueManagerInterface::class);

function gracefulShutdown(QueueManagerInterface $queueManager): never
{
    echo "Gracefully stopping...\n";

    $queueManager->disconnect();

    exit(0);
}

pcntl_signal(SIGINT, fn () => gracefulShutdown($queueManager));

$queueManager->consume('app');
