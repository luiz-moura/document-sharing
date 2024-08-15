<?php

if (! function_exists('config')) {
    function config(string $key): mixed
    {
        $keys = explode('.', $key);
        $filename = array_shift($keys);

        $config = require __DIR__ . "/../../../config/{$filename}.php";

        if (!$keys) {
            return $config;
        }

        return array_reduce($keys, function($carry, $key) use ($config) {
            if (!$carry) {
                return $config[$key];
            }

            return $carry[$key];
        });
    }
}
