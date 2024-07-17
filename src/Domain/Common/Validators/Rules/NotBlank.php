<?php

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validation;
use App\Domain\Common\Validators\Exceptions\ValidationException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotBlank implements Validation
{
    public function validate(string $propertyName, mixed $value, mixed ...$args): mixed
    {
        if (empty($value)) {
            throw new ValidationException("{$propertyName} cant be blank");
        }

        return $value;
    }
}
