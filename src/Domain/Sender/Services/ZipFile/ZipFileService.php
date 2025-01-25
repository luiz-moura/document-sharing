<?php

namespace App\Domain\Sender\Services\ZipFile;

use App\Domain\Sender\Services\ZipFile\Exceptions\ZipFileException;
use Psr\Http\Message\UploadedFileInterface;
use ZipArchive;

class ZipFileService
{
    /**
     * @param UploadedFileInterface[] $files
     */
    public function zipFiles(array $files, string $zipPath, string $zipName, ?string $zipPassword = null): string
    {
        $path = sprintf('%s/%s', $zipPath, $zipName);

        $zip = new ZipArchive();
        if (! $zipArchiveErrorCode = $zip->open($path, ZipArchive::CREATE)) {
            throw ZipFileException::fromErrorCode($zipArchiveErrorCode);
        }

        foreach ($files as $file) {
            $zip->addFile($file->getStream()->getMetadata('uri'), $file->getClientFilename());
        }

        if ($zipPassword) {
            $zip->setPassword($zipPassword);
        }

        return $path;
    }
}
