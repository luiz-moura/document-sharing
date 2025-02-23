<?php

namespace App\Domain\Common\Queue\Contracts;

interface Dispatchable
{
    public function dispatch(): void;
}
