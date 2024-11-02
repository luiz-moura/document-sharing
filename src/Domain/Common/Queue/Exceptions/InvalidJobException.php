<?php

namespace Src\Domain\Common\Queue\Exceptions;

use Exception;

class InvalidJobException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Invalid job.',
            code: 400
        );
    }
}
