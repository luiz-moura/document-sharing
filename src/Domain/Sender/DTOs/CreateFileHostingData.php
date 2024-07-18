<?php

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusEnum;

class CreateFileHostingData
{
    public function __construct(
        public int $fileId,
        public HostingData $hosting,
        public FileStatusEnum $status = FileStatusEnum::TO_SEND,
    ) {}
}
