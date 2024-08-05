<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Throwable;

class HostingNotFoundException extends Exception implements Throwable
{
    public function __construct()
    {
        parent::__construct(
            'Hosting not found.',
            code: 400
        );
    }
}
