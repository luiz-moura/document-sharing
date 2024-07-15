<?php

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;

interface SenderService
{
    public function send(UploadedFileInterface $uploadedFile): HostedFileData;
}
