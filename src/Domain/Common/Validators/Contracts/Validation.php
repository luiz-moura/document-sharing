<?php

namespace App\Domain\Common\Validators\Contracts;

interface Validation
{
    public function validate(string $propertyName, mixed $value, mixed ...$args): mixed;
}
