<?php

namespace App\Domain\Common\Factories\Contracts;

interface SimpleFactory
{
    public static function create(string $type): object;
}
