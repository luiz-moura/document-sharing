<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusEnum;

class UpdateAccessLinkFileHostingData
{
    public function __construct(
        public string $externalFileId,
        public string $webViewLink,
        public string $webContentLink,
        public FileStatusEnum $status = FileStatusEnum::SEND_SUCCESS
    ) {}
}
