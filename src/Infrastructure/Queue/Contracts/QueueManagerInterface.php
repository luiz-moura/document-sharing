<?php

namespace App\Infrastructure\Queue\Contracts;

use App\Domain\Common\Queue\Contracts\Consumer;
use App\Domain\Common\Queue\Contracts\Publisher;

interface QueueManagerInterface extends Publisher, Consumer
{
    public function __destruct();
}
