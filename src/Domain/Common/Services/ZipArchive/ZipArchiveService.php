<?php

namespace App\Domain\Common\Services\ZipArchive;

use App\Domain\Common\Services\ZipArchive\Exceptions\ZipArchiveException;
use Psr\Http\Message\UploadedFileInterface;
use ZipArchive;

class ZipArchiveService
{
    private ZipArchive $zipArchive;

    public function __construct()
    {
        $this->zipArchive = new ZipArchive();
    }

    /**
     * @param UploadedFileInterface[] $files
     */
    public function zipArchive(array $files, ?string $zipPassword = null): string
    {
        $temp = tmpfile();
        $tempPath = stream_get_meta_data($temp)['uri'];
        unlink($tempPath);

        if (! $zipArchiveErrorCode = $this->zipArchive->open($tempPath, ZipArchive::CREATE)) {
            throw ZipArchiveException::fromErrorCode($zipArchiveErrorCode);
        }

        foreach ($files as $file) {
            $this->zipArchive->addFromString($file->getStream()->getMetadata('uri'), $file->getClientFilename());
            $this->zipArchive->setCompressionName($file->getClientFilename(), ZipArchive::CM_DEFLATE);
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
