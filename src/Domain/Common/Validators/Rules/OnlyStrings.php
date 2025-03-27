<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Rules;

use App\Domain\Common\Validators\Contracts\Validator;
use App\Domain\Common\Validators\Exceptions\ValidatorException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class OnlyStrings implements Validator
{
    public function validate(string $propertyName, mixed $propertyValue, mixed ...$args): mixed
    {
        if (! is_iterable($propertyValue)) {
            throw new ValidatorException("Only integer values are allowed in the {$propertyName} field.");
        }

        $checkTyping = [
            'integer' => fn ($input): bool => filter_var($input, FILTER_VALIDATE_INT) !== false,
            'float' => fn ($input): bool => filter_var($input, FILTER_VALIDATE_FLOAT) !== false,
            'boolean' => fn ($input): bool => filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
        ];

        foreach ($propertyValue as $item) {
            if (array_reduce($checkTyping, fn ($carry, $checkType): bool => $carry || $checkType($item), initial: false)) {
                throw new ValidatorException("Only string values are allowed in the {$propertyName} field.");
            }
        }

        return $propertyValue;
    }
}
