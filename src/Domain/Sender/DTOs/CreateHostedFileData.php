<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusEnum;

class CreateHostedFileData
{
    public function __construct(
        public int $fileId,
        public HostingData $hosting,
        public FileStatusEnum $status = FileStatusEnum::TO_SEND,
    ) {}
}
