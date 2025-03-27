<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators;

use App\Domain\Common\Validators\Contracts\Validator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class PropertyValidator
{
    public function validate(object $object): void
    {
        $reflectionClass = new ReflectionClass($object);

        /**
         * @var ReflectionProperty $property
         */
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(
                name: Validator::class,
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

    // TODO: implement this method
    public static function entityFactory(string $class, array $propertyValues): void
    {
        $reflectionClass = new ReflectionClass($class);

        foreach ($propertyValues as $property => $value) {
            $reflectionProperty = $reflectionClass->getProperty($property);

            $attributes = $reflectionProperty->getAttributes();
            if (! $attributes) {
                continue;
            }

            foreach ($reflectionProperty->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if ($attributeInstance instanceof Validator) {
                    $attributeInstance->validate($property, $value);
                }
            }
        }
    }
}
