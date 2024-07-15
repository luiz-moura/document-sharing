<?php

namespace App\Domain\Sender\DTOs;

use App\Domain\Common\DTOs\DataTransferObject;
use Psr\Http\Message\UploadedFileInterface;

class UploadRequestData extends DataTransferObject
{
    public function __construct(
        public ?array $hostingIds,
        public ?UploadedFileInterface $upladedFile,
    ) {
    }
}
