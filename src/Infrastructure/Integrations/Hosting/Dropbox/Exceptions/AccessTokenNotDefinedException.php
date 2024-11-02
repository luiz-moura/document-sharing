<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox\Exceptions;

use Exception;

class AccessTokenNotDefinedException extends Exception
{
    public function __construct()
    {
        parent::__construct('The access token is not defined or expired.');
    }
}
