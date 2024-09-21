<?php

require __DIR__ . '/../../../../app/bootstrap.php';

use App\Infrastructure\Queue\Contracts\QueueManagerInterface;

/** @var QueueManagerInterface */
$queueManager = $container->get(QueueManagerInterface::class);

function gracefulShutdown(QueueManagerInterface $queueManager): never
{
    echo "Gracefully stopping...\n";

    $queueManager->__destruct();

    exit(0);
}

pcntl_signal(SIGINT, fn () => gracefulShutdown($queueManager));

$queueManager->consume('app');
