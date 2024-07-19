<?php

namespace App\Domain\Sender\DTOs;

class UpdateAcessLinkFileHostingData
{
    public function __construct(
        public int $externalFileId,
        public string $webViewLink,
        public string $webContentLink,
    ) {}
}
