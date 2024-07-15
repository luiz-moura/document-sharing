<?php

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusEnum;

class CreateFileHostingData
{
    public function __construct(
        private int $fileId,
        private HostingData $hosting,
        private FileStatusEnum $status = FileStatusEnum::TO_SEND,
    ) {}
}
