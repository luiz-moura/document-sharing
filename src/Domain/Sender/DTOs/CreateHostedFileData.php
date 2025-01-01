<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusOnHostEnum;

class CreateHostedFileData
{
    public function __construct(
        public readonly int $fileId,
        public readonly int $hostingId,
        public readonly FileStatusOnHostEnum $status = FileStatusOnHostEnum::TO_SEND,
    ) {
    }
}
