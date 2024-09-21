<?php

declare(strict_types=1);

namespace App\Domain\Sender\Enums;

enum FileStatusEnum: string
{
    case TO_SEND = 'to_send';
    case PROCESSING = 'processing';
    case SEND_FAILURE = 'send_failure';
    case SEND_SUCCESS = 'send_success';
}
