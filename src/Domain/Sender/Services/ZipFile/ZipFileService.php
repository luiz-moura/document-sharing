<?php

namespace App\Domain\Sender\Services\ZipFile;

use App\Domain\Sender\Services\ZipFile\Exceptions\ZipFileException;
use Psr\Http\Message\UploadedFileInterface;
use ZipArchive;

class ZipFileService
{
    private ZipArchive $zipArchive;

    public function __construct()
    {
        $this->zipArchive = new ZipArchive();
    }

    /**
     * @param UploadedFileInterface[] $files
     */
    public function zipFiles(array $files, ?string $zipPassword = null): string
    {
        $temp = tmpfile();
        $tempPath = stream_get_meta_data($temp)['uri'];
        unlink($tempPath);

        if (! $zipArchiveErrorCode = $this->zipArchive->open($tempPath, ZipArchive::CREATE)) {
            throw ZipFileException::fromErrorCode($zipArchiveErrorCode);
        }

        foreach ($files as $file) {
            $this->zipArchive->addFile($file->getStream()->getMetadata('uri'), $file->getClientFilename());
        }

        if ($zipPassword) {
            $this->zipArchive->setPassword($zipPassword);
        }

        $this->zipArchive->close();

        $resource = fopen($tempPath, 'r');
        $content = stream_get_contents($resource);
        fclose($temp);

        return $content;
    }
}
