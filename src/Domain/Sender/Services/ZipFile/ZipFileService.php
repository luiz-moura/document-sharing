<?php

namespace App\Domain\Sender\Services\ZipFile;

use App\Domain\Sender\Services\ZipFile\Exceptions\ZipFileException;
use ZipArchive;

class ZipFileService
{
    public function zipFiles(array $files, string $zipPath, string $zipName, ?string $zipPassword = null): string
    {
        $path = sprintf('%s/%s', $zipPath, $zipName);

        $zip = new ZipArchive();
        if (! $zipArchiveErrorCode = $zip->open($path, ZipArchive::CREATE)) {
            throw ZipFileException::fromErrorCode($zipArchiveErrorCode);
        }

        foreach ($files as $file) {
            $zip->addFile($file);
        }

        if ($zipPassword) {
            $zip->setPassword($zipPassword);
        }

        return $path;
    }
}
