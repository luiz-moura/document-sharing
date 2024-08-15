<?php

return [
    'driver' => 'pdo_pgsql',
    'user' => $_ENV['POSTGRES_USER'],
    'password' => $_ENV['POSTGRES_PASSWORD'],
    'dbname' => $_ENV['POSTGRES_DB'],
    'host' => $_ENV['POSTGRES_HOST'],
    'port' => $_ENV['POSTGRES_PORT'],
];
