<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function (): Settings {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'tmpDir' => realpath(__DIR__ . '/../tmp'),
                ...(function (): array {
                    $configs = [];

                    $files = glob(__DIR__ . "/../config/*.php");

                    foreach ($files as $file) {
                        $filename = basename($file, '.php');
                        $configs[$filename] = include $file;
                    }

                    return $configs;
                })()
            ]);
        }
    ]);
};
