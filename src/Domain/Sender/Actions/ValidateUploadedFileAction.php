<?php

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Exceptions\InvalidUploadedFileException;
use Psr\Http\Message\UploadedFileInterface;

class ValidateUploadedFileAction
{
    public const array ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png'];

    public const int MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public array $allowedMimeTypes = self::ALLOWED_MIME_TYPES;

    public int $maxFilesize = self::MAX_FILE_SIZE;

    /**
     * @throws InvalidUploadedFileException
     */
    public function __invoke(UploadedFileInterface $uploadedFile): void
    {
        $filename = $uploadedFile->getClientFilename();

        if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
            throw InvalidUploadedFileException::fromUploadErrorCode($uploadedFile->getError(), $filename);
        }

        if (! $uploadedFile->getSize()) {
            throw new InvalidUploadedFileException('Invalid file content', $filename);
        }

        if ($uploadedFile->getSize() > $this->maxFilesize) {
            throw new InvalidUploadedFileException('File size is too large', $filename);
        }

        if (! in_array($uploadedFile->getClientMediaType(), $this->allowedMimeTypes)) {
            throw new InvalidUploadedFileException('Invalid file type', $filename);
        }
    }
}
