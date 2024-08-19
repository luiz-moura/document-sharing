<?php

declare(strict_types=1);

namespace App\Domain\Sender\DTOs;

use App\Domain\Common\DTOs\DataTransferObject;
use App\Domain\Common\Validators\Rules\NotBlank;
use Psr\Http\Message\UploadedFileInterface;

class UploadRequestData extends DataTransferObject
{
    public function __construct(
        /** @var string[] */
        #[NotBlank]
        public ?array $hostingSlugs,
        #[NotBlank]
        public ?UploadedFileInterface $uploadedFile,
    ) {
        parent::__construct();
    }
}
