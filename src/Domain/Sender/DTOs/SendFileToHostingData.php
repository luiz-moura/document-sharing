<?php

namespace App\Domain\Sender\DTOs;

use Psr\Http\Message\UploadedFileInterface;

class SendFileToHostingData
{
    public function __construct(
        public int $fileHostingId,
        public HostingData $hosting,
        public UploadedFileInterface $uploadedFile,
    ) {}
}
