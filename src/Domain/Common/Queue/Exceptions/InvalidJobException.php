<?php

namespace Src\Domain\Common\Queue\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class InvalidJobException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Invalid job.',
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }
}
