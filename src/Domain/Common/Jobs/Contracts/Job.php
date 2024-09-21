<?php

declare(strict_types=1);

namespace App\Domain\Common\Jobs\Contracts;

interface Job
{
    public function setArgs(mixed ...$args): static;
    public function handle(): void;
    public function dispatch(): void;
}
