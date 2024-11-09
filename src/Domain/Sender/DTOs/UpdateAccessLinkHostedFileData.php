<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Sender\Enums\FileStatusOnHostEnum;

class UpdateAccessLinkHostedFileData
{
    public function __construct(
        public string $externalFileId,
        public string $webViewLink,
        public string $webContentLink,
        public readonly FileStatusOnHostEnum $status = FileStatusOnHostEnum::SEND_SUCCESS
    ) {
    }
}
