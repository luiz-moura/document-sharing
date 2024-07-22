<?php

declare(strict_types=1);

namespace Tests\Utils\Faker;

use Faker\Factory;
use Faker\Generator;

function faker(string $locale = null): Generator
{
    return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
}
