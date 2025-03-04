<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validation;
use App\Domain\Common\Validators\Exceptions\ValidationException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class OnlyNumbers implements Validation
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed
    {
        if (! is_iterable($propertyValue)) {
            throw new ValidationException("Only integer values are allowed in the {$propertyName} field.");
        }

        foreach ($propertyValue as $item) {
            if (! is_int($item)) {
                throw new ValidationException("Only integer values are allowed in the {$propertyName} field.");
            }
        }

        return $propertyValue;
    }
}
