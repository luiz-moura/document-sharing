<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Contracts;

interface Validator
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed;
}
