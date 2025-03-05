<?php

declare(strict_types=1);

namespace App\Domain\Sender\Services\ZipFile\Exceptions;

use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use ZipArchive;

class ZipFileException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            code: StatusCode::STATUS_BAD_REQUEST
        );
    }

    public static function fromErrorCode(int $errorCode): self
    {
        $message = match ($errorCode) {
            ZipArchive::ER_OK => 'N No error',
            ZipArchive::ER_MULTIDISK => 'N Multi-disk zip archives not supported',
            ZipArchive::ER_RENAME => 'S Renaming temporary file failed',
            ZipArchive::ER_CLOSE => 'S Closing zip archive failed',
            ZipArchive::ER_SEEK => 'S Seek error',
            ZipArchive::ER_READ => 'S Read error',
            ZipArchive::ER_WRITE => 'S Write error',
            ZipArchive::ER_CRC => 'N CRC error',
            ZipArchive::ER_ZIPCLOSED => 'N Containing zip archive was closed',
            ZipArchive::ER_NOENT => 'N No such file',
            ZipArchive::ER_EXISTS => 'N File already exists',
            ZipArchive::ER_OPEN => 'S Can\'t open file',
            ZipArchive::ER_TMPOPEN => 'S Failure to create temporary file',
            ZipArchive::ER_ZLIB => 'Z Zlib error',
            ZipArchive::ER_MEMORY => 'N Malloc failure',
            ZipArchive::ER_CHANGED => 'N Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'N Compression method not supported',
            ZipArchive::ER_EOF => 'N Premature EOF',
            ZipArchive::ER_INVAL => 'N Invalid argument',
            ZipArchive::ER_NOZIP => 'N Not a zip archive',
            ZipArchive::ER_INTERNAL => 'N Internal error',
            ZipArchive::ER_INCONS => 'N Zip archive inconsistent',
            ZipArchive::ER_REMOVE => 'S Can\'t remove file',
            ZipArchive::ER_DELETED => 'N Entry has been deleted',
            default => sprintf('Unknown status %s', $errorCode),
        };

        return new self($message);
    }
}
