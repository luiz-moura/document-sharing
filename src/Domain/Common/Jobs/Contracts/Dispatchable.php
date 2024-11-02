<?php

namespace App\Domain\Common\Jobs\Contracts;

interface Dispatchable
{
    public function dispatch(): void;
}
