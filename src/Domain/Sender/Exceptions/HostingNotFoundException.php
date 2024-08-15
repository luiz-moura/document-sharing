<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Throwable;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class HostingNotFoundException extends Exception implements Throwable
{
    public function __construct()
    {
        parent::__construct(
            'Hosting not found.',
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }
}
