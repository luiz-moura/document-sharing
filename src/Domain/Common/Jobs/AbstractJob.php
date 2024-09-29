<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Queue\Contracts\Publisher;

abstract class AbstractJob
{
    protected Publisher $publisher;

    protected mixed $args;

    abstract public function handle(): void;

    public function setArgs(mixed ...$args): static
    {
        $this->args = $args;

        return $this;
    }

    public function dispatch(): void
    {
        $job = [
            'class' => static::class,
            'args' => $this->args,
        ];

        $this->publisher->publish($job, 'app');
    }
}
