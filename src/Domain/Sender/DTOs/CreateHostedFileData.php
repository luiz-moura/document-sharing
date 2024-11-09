<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusOnHostEnum;

class CreateHostedFileData
{
    public function __construct(
        public int $fileId,
        public int $hostingId,
        public FileStatusOnHostEnum $status = FileStatusOnHostEnum::TO_SEND,
    ) {
    }
}
