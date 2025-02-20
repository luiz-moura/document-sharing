<?php

return [
    'max_retries' => $_ENV['QUEUE_MAX_RETRIES'] ?? 5,
    'retry_delay_seconds' => $_ENV['QUEUE_RETRY_DELAY_SECONDS'] ?? 2,

    'host' => $_ENV['RABBITMQ_HOST'],
    'port' => $_ENV['RABBITMQ_PORT'],
    'user' => $_ENV['RABBITMQ_USER'],
    'password' => $_ENV['RABBITMQ_PASSWORD'],
];
