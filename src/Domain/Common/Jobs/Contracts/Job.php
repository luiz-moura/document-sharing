<?php

namespace App\Domain\Common\Jobs\Contracts;

interface Job
{
    public function handle(): void;
    public function dispatch(mixed ...$args): void;
}
