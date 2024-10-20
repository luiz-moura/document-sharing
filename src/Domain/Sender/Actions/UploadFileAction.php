<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Common\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadRequestData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Exceptions\InvalidFileException;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private HostedFileRepository $fileHostRepository,
        private HostingRepository $hostingRepository,
        private SendFileToHostingJob $sendFileToHostingJob,
        private UuidGeneratorService $uuidGeneratorService,
    ) {
    }

    public function __invoke(UploadRequestData $uploadRequest): string
    {
        $this->validateUploadedFile($uploadRequest->uploadedFile);

        $hosts = $this->queryHostingByIds($uploadRequest->hostingSlugs);
        $uuid = $this->uuidGeneratorService->generateUuid();

        $uploadFile = &$uploadRequest->uploadedFile;
        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $uuid,
                $uploadFile->getClientFilename(),
                $uploadFile->getSize(),
                $uploadFile->getClientMediaType(),
            )
        );

        foreach ($hosts as $hosting) {
            $hostedFileId = $this->fileHostRepository->create(
                new CreateHostedFileData(
                    $fileId,
                    $hosting
                )
            );

            $this->sendFileToHostingJob->setArgs(
                new SendFileToHostingData(
                    $hosting,
                    $hostedFileId,
                    encodedFile: new EncodedFileData(
                        filename: $uploadFile->getClientFilename(),
                        mediaType: $uploadFile->getClientMediaType(),
                        size:  $uploadFile->getSize(),
                        base64:$uploadFile->getStream()->__toString(),
                    )
                )
            )->dispatch();
        }

        return $uuid;
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
     * @param string[] $hostingSlugs
     * @return HostingData[]
     * @throws HostingNotFoundException
     */
    private function queryHostingByIds(array $hostingSlugs): array
    {
        $hosts = $this->hostingRepository->queryBySlugs($hostingSlugs);

        if (count($hosts) !== count($hostingSlugs)) {
            throw new HostingNotFoundException();
        }

        return $hosts;
    }
}
