<?php

namespace App\Domain\Common\Queue;

use App\Domain\Common\Queue\Contracts\Job as JobContract;

abstract class Job implements JobContract
{
    public const string DEFAULT_QUEUE_NAME = 'app';

    public const int DEFAULT_MAX_RETRIES = 3;

    public const int DEFAULT_RETRY_DELAY_SECONDS = 5;

    public const int DEFAULT_ATTEMPTS = 0;

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
