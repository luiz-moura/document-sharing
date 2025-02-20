<?php

declare(strict_types=1);

namespace App\Application\Settings;

class Settings implements SettingsInterface
{
    public function __construct(private array $settings)
    {
    }

    public function get(string $key = ''): mixed
    {
        $keys = explode('.', $key);
        $config = array_shift($keys);

        $config = $this->settings[$config];

        if (! $keys) {
            return $config;
        }

        return array_reduce($keys, function ($carry, $key) use ($config): mixed {
            if (! $carry) {
                return $config[$key];
            }

            return $carry[$key];
        });
    }
}
