<?php

declare(strict_types=1);

namespace App\Domain\Common\Jobs\Contracts;

interface Job
{
    public function handle(): void;
    public function dispatch(mixed ...$args): void;
}
