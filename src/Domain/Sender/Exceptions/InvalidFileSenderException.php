<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class InvalidFileSenderException extends Exception
{
    public const int CODE = StatusCode::STATUS_BAD_REQUEST;

    public function __construct(string $name)
    {
        parent::__construct(
            sprintf('Invalid file sender: %s.', $name),
            self::CODE
        );
    }
}
