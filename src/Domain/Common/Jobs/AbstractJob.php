<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Jobs\Contracts\Dispatchable;
use App\Domain\Common\Jobs\Contracts\Job;
use App\Domain\Common\Queue\Contracts\Publisher;
use DI\Attribute\Inject;

abstract class AbstractJob implements Job, Dispatchable
{
    #[Inject]
    /** @phpstan-ignore-next-line */
    private Publisher $publisher;

    private string $queue = 'app';

    protected int $maxRetries = 3;

    protected int $retryDelaySeconds = 5;

    protected array $args;

    protected int $attempts = 0;

    public function setArgs(mixed ...$args): self
    {
        $this->args = $args;

        return $this;
    }

    public function setQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    abstract public function handle(): void;

    public function dispatch(): void
    {
        $this->publisher->publish($this, $this->queue);
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    public function shouldRetry(): bool
    {
        return $this->attempts < $this->maxRetries;
    }

    public function __serialize(): array
    {
        return [
            'queue' => $this->queue,
            'maxRetries' => $this->maxRetries,
            'retryDelaySeconds' => $this->retryDelaySeconds,
            'args' => $this->args,
            'attempts' => $this->attempts,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        $this->queue = $serialized['queue'];
        $this->maxRetries = $serialized['maxRetries'];
        $this->retryDelaySeconds = $serialized['retryDelaySeconds'];
        $this->args = $serialized['args'];
        $this->attempts = $serialized['attempts'];
    }
}
