<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class HostingNotFoundException extends Exception
{
    public function __construct(string $message = 'Hosting not found.')
    {
        parent::__construct(
            $message,
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }

    public static function fromHostingNotFound(array $hosts): self
    {
        $message = sprintf('Hosting not found: %s', implode(', ', $hosts));

        return new self($message);
    }
}
