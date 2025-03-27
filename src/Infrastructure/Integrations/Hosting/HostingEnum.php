<?php

namespace App\Infrastructure\Integrations\Hosting\Enums;

enum HostingEnum: string
{
    case DROPBOX = 'dropbox';
    case IN_MEMORY = 'in-memory';
}
