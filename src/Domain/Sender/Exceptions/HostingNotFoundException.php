<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class HostingNotFoundException extends Exception
{
    public const int CODE = StatusCode::STATUS_UNPROCESSABLE_ENTITY;

    public function __construct(string|int $hosting)
    {
        parent::__construct(
            sprintf('Hosting not found: %s.', $hosting),
            self::CODE
        );
    }
}
