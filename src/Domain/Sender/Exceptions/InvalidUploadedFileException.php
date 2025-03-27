<?php

declare(strict_types=1);

namespace App\Domain\Sender\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class InvalidUploadedFileException extends Exception
{
    public const int CODE = StatusCode::STATUS_UNPROCESSABLE_ENTITY;

    public function __construct(string $message, string $filename)
    {
        parent::__construct(
            sprintf('%s, filename: %s', $message, $filename),
            self::CODE
        );
    }

    public static function fromUploadErrorCode(int $code, string $filename): self
    {
        return new self(
            self::getMessageByCode($code),
            $filename
        );
    }

    private static function getMessageByCode(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE  => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            default => sprintf('Unknown error: %s', $code),
        };
    }
}
