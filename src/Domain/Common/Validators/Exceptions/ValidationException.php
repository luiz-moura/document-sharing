<?php

declare(strict_types=1);

namespace App\Domain\Common\Validators\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class ValidationException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            code: StatusCode::STATUS_UNPROCESSABLE_ENTITY
        );
    }
}
