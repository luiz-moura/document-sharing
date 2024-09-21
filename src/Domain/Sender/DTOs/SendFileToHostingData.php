<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

class SendFileToHostingData
{
    public function __construct(
        public HostingData $hosting,
        public int $hostedFileId,
        public EncodedFileData $encodedFile,
    ) {
    }
}
