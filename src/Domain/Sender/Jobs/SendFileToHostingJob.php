<?php

declare(strict_types=1);

namespace App\Domain\Sender\Jobs;

use App\Domain\Common\Jobs\AbstractJob;
use App\Domain\Common\Jobs\Contracts\Job;
use App\Domain\Common\Queue\Contracts\Publisher;
use App\Domain\Sender\Actions\SendFileToHostingAction;

class SendFileToHostingJob extends AbstractJob implements Job
{
    public function __construct(
        private Publisher $publisher,
        private SendFileToHostingAction $sendFileToHostingAction,
    ) {
        parent::__construct($publisher);
    }

    public function handle(): void
    {
        ($this->sendFileToHostingAction)(...$this->args);
    }
}
