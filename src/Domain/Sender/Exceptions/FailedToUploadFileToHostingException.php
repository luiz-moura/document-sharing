<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Throwable;

class FailedToUploadFileToHostingException extends Exception
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            'Failed to upload file to hosting.',
            code: StatusCode::STATUS_BAD_REQUEST,
            previous: $previous
        );
    }
}
