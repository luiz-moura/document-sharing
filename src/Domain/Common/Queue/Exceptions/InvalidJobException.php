<?php

namespace Src\Domain\Common\Queue\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class InvalidJobException extends Exception
{
    public const int CODE = StatusCode::STATUS_BAD_REQUEST;

    public function __construct()
    {
        parent::__construct(
            'Invalid job.',
            self::CODE
        );
    }
}
