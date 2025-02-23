<?php

namespace App\Domain\Common\Queue;

use App\Domain\Common\Queue\Contracts\Job;
use App\Domain\Common\Queue\Contracts\Publisher;

class Dispatcher
{
    public function __construct(
        private readonly Publisher $publisher
    ) {
    }

    /**
     * @property Job[] $jobs
     */
    private array $jobs = [];

    public function addJob(Job $job): self
    {
        $this->jobs[] = $job;

        return $this;
    }

    public function dispatch(): void
    {
        foreach ($this->jobs as $job) {
            $this->publisher->publish($job, $job->getQueue());
        }
    }
}
