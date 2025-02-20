<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validation;
use App\Domain\Common\Validators\Exceptions\ValidationException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class NotBlank implements Validation
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed
    {
        if (empty($propertyValue)) {
            throw new ValidationException("{$propertyName} cant be blank");
        }

        return $propertyValue;
    }
}
