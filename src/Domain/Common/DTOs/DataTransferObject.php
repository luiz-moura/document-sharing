<?php

declare(strict_types=1);

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Validators\AttributeValidator;

abstract class DataTransferObject
{
    public function __construct()
    {
        $this->validate();
    }

    private function validate(): void
    {
        // TODO: remove liability from dto
        $validator = new AttributeValidator();
        $validator->validate($this);
    }
}
