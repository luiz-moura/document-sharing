<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\FileHostingData;

interface FileSenderService
{
    public function send(EncodedFileData $encodedFile): FileHostingData;
}
