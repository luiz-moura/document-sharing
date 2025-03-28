<?php

namespace App\Infrastructure\Integrations\Hosting;

enum HostingEnum: string
{
    case DROPBOX = 'dropbox';
    case IN_MEMORY = 'in-memory';
}
