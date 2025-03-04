<?php

return [
    'host' => $_ENV['QUEUE_HOST'],
    'port' => $_ENV['QUEUE_PORT'],
    'user' => $_ENV['QUEUE_USER'],
    'password' => $_ENV['QUEUE_PASSWORD'],
    'max_retries' => $_ENV['QUEUE_MAX_RETRIES'] ?? 5,
    'retry_delay_seconds' => $_ENV['QUEUE_RETRY_DELAY_SECONDS'] ?? 2,
];
