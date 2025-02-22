<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks;

abstract class MockFactory
{
    abstract public static function create(array $custom = []): object;
    abstract public static function make(): array;
}
