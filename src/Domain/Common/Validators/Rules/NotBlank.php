<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validator;
use App\Domain\Common\Validators\Exceptions\ValidatorException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class NotBlank implements Validator
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed
    {
        if (empty($propertyValue)) {
            throw new ValidatorException("{$propertyName} cant be blank");
        }

        return $propertyValue;
    }
}
