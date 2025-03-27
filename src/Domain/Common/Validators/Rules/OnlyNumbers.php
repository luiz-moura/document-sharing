<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validator;
use App\Domain\Common\Validators\Exceptions\ValidatorException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class OnlyNumbers implements Validator
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed
    {
        if (! is_iterable($propertyValue)) {
            throw new ValidatorException("Only integer values are allowed in the {$propertyName} field.");
        }

        foreach ($propertyValue as $item) {
            if (! is_int($item)) {
                throw new ValidatorException("Only integer values are allowed in the {$propertyName} field.");
            }
        }

        return $propertyValue;
    }
}
