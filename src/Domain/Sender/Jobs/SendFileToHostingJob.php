<?php

namespace App\Domain\Sender\Jobs;

use App\Domain\Common\Jobs\Contracts\Job;

class SendFileToHostingJob implements Job
{

    public function __construct(
    ) {}

    public function handle(): void
    {
    }

    public function dispatch(mixed ...$args): void
    {
    }
}
