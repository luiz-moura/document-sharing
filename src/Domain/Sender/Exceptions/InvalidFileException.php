<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class InvalidFileException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }

    public static function fromErrorCode(int $errorCode, string $filename): self
    {
        $message = match ($errorCode) {
            UPLOAD_ERR_INI_SIZE  => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => sprintf('Unknown error %s', $errorCode),
        };

        return new self(
            sprintf('Error uploading file: %s, %s', $filename, $message)
        );
    }
}
