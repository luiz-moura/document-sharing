<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Jobs\Contracts\Dispatchable;
use App\Domain\Common\Queue\Contracts\Job;
use App\Domain\Common\Queue\Contracts\Publisher;
use DI\Attribute\Inject;

abstract class AbstractJob implements Job, Dispatchable
{
    public const string DEFAULT_QUEUE_NAME = 'app';

    public const int DEFAULT_MAX_RETRIES = 3;

    public const int DEFAULT_RETRY_DELAY_SECONDS = 5;

    public const int DEFAULT_ATTEMPTS = 0;

    #[Inject]
    /** @phpstan-ignore-next-line */
    private readonly Publisher $publisher;

    protected string $queue = self::DEFAULT_QUEUE_NAME;

    protected int $maxRetries = self::DEFAULT_MAX_RETRIES;

    protected int $retryDelaySeconds = self::DEFAULT_RETRY_DELAY_SECONDS;

    protected int $attempts = self::DEFAULT_ATTEMPTS;

    protected array $args;

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
            'attempts' => $this->attempts,
            'args' => $this->args,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        $this->queue = $serialized['queue'];
        $this->maxRetries = $serialized['maxRetries'];
        $this->retryDelaySeconds = $serialized['retryDelaySeconds'];
        $this->attempts = $serialized['attempts'];
        $this->args = $serialized['args'];
    }
}
