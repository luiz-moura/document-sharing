<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Common\DTOs\AbstractDataTransferObject;
use App\Domain\Common\Validators\Rules\NotBlank;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @property string[] $hostingSlugs
 * @property UploadedFileInterface[] $uploadedFiles
 */
class UploadRequestData extends AbstractDataTransferObject
{
    public function __construct(
        #[NotBlank]
        public readonly array $hostingSlugs,
        #[NotBlank]
        public readonly array $uploadedFiles,
        public readonly bool $shouldZip,
    ) {
        parent::__construct();
    }
}
