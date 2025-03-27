<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class SendFileToHostingData
{
    public function __construct(
        public readonly string $hostingSlug,
        public readonly int $fileHostingId,
        public readonly EncodedFileData $encodedFile,
    ) {
    }
}
