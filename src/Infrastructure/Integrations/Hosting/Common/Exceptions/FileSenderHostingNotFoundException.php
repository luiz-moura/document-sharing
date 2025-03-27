<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Hosting\Common\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class FileSenderHostingNotFoundException extends Exception
{
    public function __construct(string $hosting)
    {
        parent::__construct(
            sprintf('Hosting %s not found.', $hosting),
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }
}
