<?php

declare(strict_types=1);

namespace App\Domain\Sender\Contracts;

use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusOnHostEnum;

interface HostedFileRepository
{
    public function create(CreateHostedFileData $hostedFile): int;
    public function updateAccessLink(int $hostedFileId, UpdateAccessLinkHostedFileData $hostedFile): void;
    public function updateStatus(int $hostedFileId, FileStatusOnHostEnum $status): void;
}
