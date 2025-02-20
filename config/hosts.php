<?php

return [
    'max_retries' => $_ENV['QUEUE_MAX_RETRIES'] ?? 5,
    'retry_delay_seconds' => $_ENV['QUEUE_RETRY_DELAY_SECONDS'] ?? 2,
    'timeout' => $_ENV['DROPBOX_TIMEOUT'],

    'dropbox' => [
        'uri' => $_ENV['DROPBOX_URI'],
        'app_key' => $_ENV['DROPBOX_APP_KEY'],
        'app_secret' => $_ENV['DROPBOX_APP_SECRET'],
        'access_code' => $_ENV['DROPBOX_ACCESS_CODE'],
    ],
];
