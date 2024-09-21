<?php

return [
    'max_retries' => (int) $_ENV['QUEUE_MAX_RETRIES'] ?? 5,
    'retry_delay_seconds' => (int) $_ENV['QUEUE_RETRY_DELAY_SECONDS'] ?? 2,

    // RABBITMQ
    'rabbitmq' => [
        'host' => $_ENV['RABBITMQ_HOST'],
        'port' => $_ENV['RABBITMQ_PORT'],
        'user' => $_ENV['RABBITMQ_USER'],
        'password' => $_ENV['RABBITMQ_PASSWORD'],
    ],
];
