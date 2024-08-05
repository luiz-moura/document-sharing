<?php

declare(strict_types=1);

namespace App\Domain\Sender\Enums;

enum FileStatusEnum: string
{
    case TO_SEND = 'to_send';
    case SEND_SUCCESS = 'send_success';
}
