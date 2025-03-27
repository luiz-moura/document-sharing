<?php

declare(strict_types=1);

namespace App\Domain\Sender\Jobs;

use App\Domain\Common\Queue\AbstractJob;
use App\Domain\Sender\Actions\SendFileToHostingAction;
use DI\Attribute\Inject;

class SendFileToHostingJob extends AbstractJob
{
    // TODO: change responsibility to app in queue manager
    #[Inject]
    /** @phpstan-ignore-next-line */
    private readonly SendFileToHostingAction $sendFileToHostingAction;

    public function handle(): void
    {
        ($this->sendFileToHostingAction)(...$this->args);
    }
}
