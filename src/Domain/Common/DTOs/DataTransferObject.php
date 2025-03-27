<?php

declare(strict_types=1);

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Validators\PropertyValidator;

abstract class DataTransferObject
{
    public function __construct()
    {
        $this->validate();
    }

    private function validate(): void
    {
        // TODO: remove liability from dto
        $propertyValidator = new PropertyValidator();
        $propertyValidator->validate($this);
    }
}
