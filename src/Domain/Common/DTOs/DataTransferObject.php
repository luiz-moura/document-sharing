<?php

namespace App\Domain\Common\DTOs;
use App\Domain\Common\Validators\Validator;


abstract class DataTransferObject
{
    public function __construct(...$args)
    {
        self::__construct(...$args);

        $this->validate();
    }

    private function validate(): void
    {
        $validator = new Validator();
        $validator->validate($this);
    }
}
