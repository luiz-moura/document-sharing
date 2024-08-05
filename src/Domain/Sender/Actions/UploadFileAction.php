<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadRequestData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Exceptions\InvalidFileException;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileHostingRepository $fileHostRepository,
        private HostingRepository $hostingRepository,
        private SendFileToHostingAction $sendFileToHostingAction,
    ) {}

    public function __invoke(UploadRequestData $uploadRequest): void
    {
        $this->validateUploadedFile($uploadRequest->uploadedFile);

        $hosts = $this->queryHostingByIds($uploadRequest->hostingIds);

        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $uploadRequest->uploadedFile->getClientFilename(),
                $uploadRequest->uploadedFile->getSize(),
                $uploadRequest->uploadedFile->getClientMediaType(),
            )
        );

        foreach ($hosts as $hosting) {
            $fileHostingId = $this->fileHostRepository->create(
                new CreateFileHostingData(
                    $fileId,
                    $hosting
                )
            );

            ($this->sendFileToHostingAction)(
                new SendFileToHostingData(
                    $fileHostingId,
                    $hosting,
                    $uploadRequest->uploadedFile,
                )
            );
        }
    }

    /**
     * @throws InvalidFileException
     */
    private function validateUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
            throw new InvalidFileException($uploadedFile->getError());
        }
    }

    /**
     * @param int[] $hostingIds
     * @return HostingData[]
     * @throws HostingNotFoundException
     */
    private function queryHostingByIds(array $hostingIds): array
    {
        $hosts = $this->hostingRepository->queryByIds($hostingIds);

        if (count($hosts) !== count($hostingIds)) {
            throw new HostingNotFoundException();
        }

        return $hosts;
    }
}
