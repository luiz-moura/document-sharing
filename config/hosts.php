<?php

return [
    'max_retries' => $_ENV['HOST_MAX_RETRIES'] ?? 3,
    'retry_delay_seconds' => $_ENV['HOST_RETRY_DELAY_SECONDS'] ?? 5,
    'timeout' => $_ENV['HOST_TIMEOUT'] ?? 10,
    'dropbox' => [
        'uri' => $_ENV['DROPBOX_URI'],
        'app_key' => $_ENV['DROPBOX_APP_KEY'],
        'app_secret' => $_ENV['DROPBOX_APP_SECRET'],
        'access_code' => $_ENV['DROPBOX_ACCESS_CODE'],
    ],
];
