<?php

declare(strict_types=1);

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Validators\AttributeValidator;

abstract class AbstractDataTransferObject
{
    public function __construct()
    {
        $this->validate();
    }

    private function validate(): void
    {
        $validator = new AttributeValidator();
        $validator->validate($this);
    }
}
