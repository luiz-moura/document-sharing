<?php

return [
    'driver' => $_ENV['DB_DRIVER'] ?? 'pdo_pgsql',
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'dbname' => $_ENV['DB_DB'],
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],

    'em_path' => realpath(__DIR__ . '/../src/Infrastructure/Persistence/Doctrine/entity-manager.php'),

    'doctrine' => [
        'table_storage' => [
            'table_name' => 'doctrine_migration_versions',
            'version_column_name' => 'version',
            'version_column_length' => 191,
            'executed_at_column_name' => 'executed_at',
            'execution_time_column_name' => 'execution_time',
        ],

        'migrations_paths' => [
            'Database\Migrations' => './src/Infrastructure/Persistence/Doctrine/Migrations',
        ],

        'all_or_nothing' => true,
        'transactional' => true,
        'check_database_platform' => true,
        'organize_migrations' => 'none',
        'connection' => null,
        'em' => null,
    ],
];
