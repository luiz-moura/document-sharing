<?php

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;

interface FileSenderService
{
    public function send(UploadedFileInterface $uploadedFile): HostedFileData;
}
