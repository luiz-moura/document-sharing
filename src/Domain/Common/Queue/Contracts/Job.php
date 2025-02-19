<?php

declare(strict_types=1);

namespace App\Domain\Common\Queue\Contracts;

interface Job
{
    public function handle(): void;
    public function setArgs(mixed ...$args): self;
    public function getAttempts(): int;
    public function getQueue(): string;
    public function incrementAttempts(): void;
    public function shouldRetry(): bool;
}
