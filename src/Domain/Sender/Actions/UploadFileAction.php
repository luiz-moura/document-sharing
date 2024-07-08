<?php

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileHostingRepository $fileHostRepository
    ) {}

    /**
     * @param int[] $hostingIds
     */
    public function __invoke(UploadedFileInterface $uploadFile, array $hostingIds): void
    {
        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $uploadFile->getClientFilename(),
                $uploadFile->getSize(),
                $uploadFile->getClientMediaType(),
            )
        );

        foreach ($hostingIds as $hostingId) {
            $this->fileHostRepository->create(
                new CreateFileHostingData(
                    $fileId,
                    $hostingId
                )
            );
        }
    }
}
