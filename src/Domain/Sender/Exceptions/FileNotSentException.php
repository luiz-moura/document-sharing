<?php

namespace App\Domain\Sender\Exceptions;

use Exception;
use Throwable;

class FileNotSentException extends Exception implements Throwable
{
    public function __construct()
    {
        parent::__construct(
            'File not sent.',
            code: 400
        );
    }
}
