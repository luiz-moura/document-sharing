<?php

namespace App\Infrastructure\Integrations\Hosting\Common\Enums;

enum HostingEnum: string
{
    case DROPBOX = 'dropbox';
    case IN_MEMORY = 'in-memory';
}
