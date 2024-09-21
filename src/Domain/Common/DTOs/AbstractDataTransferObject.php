<?php

declare(strict_types=1);

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Validators\Validator;

abstract class AbstractDataTransferObject
{
    public function __construct()
    {
        $this->validate();
    }

    private function validate(): void
    {
        $validator = new Validator();
        $validator->validate($this);
    }
}
