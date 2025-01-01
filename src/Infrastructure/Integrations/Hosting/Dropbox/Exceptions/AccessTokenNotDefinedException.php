<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class AccessTokenNotDefinedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'The access token is not defined or expired.',
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }
}
