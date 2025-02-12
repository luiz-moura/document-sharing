<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

use App\Domain\Common\Validators\Contracts\Validation;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class AttributeValidator
{
    public function validate(object $object): void
    {
        $reflectionClass = new ReflectionClass($object);

        /**
         * @var ReflectionProperty $property
         */
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(
                name: Validation::class,
                flags: ReflectionAttribute::IS_INSTANCEOF
            );

            if (!$attributes) {
                return;
            }

            foreach ($attributes as $attribute) {
                $validator = $attribute->newInstance();

                $validator->validate(
                    $property->getName(),
                    $property->getValue($object),
                    ...$attribute->getArguments()
                );
            }
        }
    }
}
