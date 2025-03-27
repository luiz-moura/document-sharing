<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Throwable;

class FailedToSendFileException extends Exception
{
    public const int CODE = StatusCode::STATUS_BAD_REQUEST;

    public function __construct(string $filename, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Failed to upload file to hosting, filename: %s', $filename),
            self::CODE,
            $previous
        );
    }
}
