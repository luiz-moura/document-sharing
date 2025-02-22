<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Common\Adapters\Contracts\UuidGeneratorService;
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
use App\Domain\Sender\Services\ZipFile\ZipFileService;
use DateTime;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileAction
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly HostedFileRepository $fileHostRepository,
        private readonly HostingRepository $hostingRepository,
        private readonly SendFileToHostingJob $sendFileToHostingJob,
        private readonly UuidGeneratorService $uuidGeneratorService,
        private readonly ZipFileService $zipFileService,
    ) {
    }

    /**
     * @return string[]
     */
    public function __invoke(UploadRequestData $uploadRequest): array
    {
        $this->validateUploadedFile($uploadRequest->uploadedFiles);

        $hosts = $this->queryHostingByIds($uploadRequest->hostingSlugs);

        if ($uploadRequest->shouldZip) {
            return [$this->zipFiles($uploadRequest->uploadedFiles, $hosts)];
        }

        return $this->sendIndividually($uploadRequest->uploadedFiles, $hosts);
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hosts
     */
    private function zipFiles(array $uploadedFiles, array $hosts): string
    {
        $fileExternalId = $this->uuidGeneratorService->generateUuid();
        $fileUploadDate = (new DateTime('now'))->format('Y-m-d_H-i-s');
        $zipName = sprintf('%s_%s_%s.%s', $fileExternalId, $fileUploadDate, count($uploadedFiles), 'zip');

        $filepath = $this->zipFileService->zipFiles($uploadedFiles, __DIR__ . '/../../../../storage/uploads', $zipName);

        $filesize = filesize($filepath);
        $mediaType = mime_content_type($filepath);

        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $fileExternalId,
                $zipName,
                $filesize,
                $mediaType,
            )
        );

        $encodedFile = new EncodedFileData(
            $zipName,
            $mediaType,
            $filesize,
            base64_encode(file_get_contents($filepath)),
        );

        $this->sendFileToHosting($fileId, $hosts, $encodedFile);

        return $fileExternalId;
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hosts
     * @return string[]
     */
    private function sendIndividually(array $uploadedFiles, array $hosts): array
    {
        $fileExternalIds = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $fileExternalId = $this->uuidGeneratorService->generateUuid();
            $fileExternalIds[] = $fileExternalId;

            $fileId = $this->fileRepository->create(
                new CreateFileData(
                    $fileExternalId,
                    $uploadedFile->getClientFilename(),
                    $uploadedFile->getSize(),
                    $uploadedFile->getClientMediaType(),
                )
            );

            $stream = $uploadedFile->getStream();
            $stream->rewind();

            $encodedFile = new EncodedFileData(
                $uploadedFile->getClientFilename(),
                $uploadedFile->getClientMediaType(),
                $uploadedFile->getSize(),
                base64_encode($stream->getContents()),
            );

            $this->sendFileToHosting($fileId, $hosts, $encodedFile);
        }

        return $fileExternalIds;
    }

    /**
     * @param HostingData[] $hosts
     */
    private function sendFileToHosting(int $fileId, array $hosts, EncodedFileData $encodedFile): void
    {
        foreach ($hosts as $hosting) {
            $hostedFileId = $this->fileHostRepository->create(
                new CreateHostedFileData(
                    $fileId,
                    $hosting->id,
                )
            );

            $this->sendFileToHostingJob->setArgs(
                new SendFileToHostingData(
                    $hosting->slug,
                    $hostedFileId,
                    $encodedFile,
                )
            )->dispatch();
        }
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @throws InvalidFileException
     */
    private function validateUploadedFile(array $uploadedFiles): void
    {
        array_walk($uploadedFiles, function (UploadedFileInterface $uploadedFile): void {
            $filename = $uploadedFile->getClientFilename();

            if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
                throw InvalidFileException::fromErrorCode($uploadedFile->getError(), $filename);
            }

            if (! $uploadedFile->getSize()) {
                throw new InvalidFileException(sprintf('Invalid file content, filename: %s', $filename));
            }

            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($uploadedFile->getSize() > $maxFileSize) {
                throw new InvalidFileException(sprintf('File size is too large, filename: %s', $filename));
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png'];
            if (! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes)) {
                throw new InvalidFileException(sprintf('Invalid file type, filename: %s', $filename));
            }
        });
    }

    /**
     * @param string[] $hostingSlugInPayload
     * @return HostingData[]
     * @throws HostingNotFoundException
     */
    private function queryHostingByIds(array $hostingSlugInPayload): array
    {
        $hosts = $this->hostingRepository->queryBySlugs($hostingSlugInPayload);
        $hostsFound = array_column($hosts, 'slug');

        if ($hostsNotFound = array_diff($hostingSlugInPayload, $hostsFound)) {
            throw HostingNotFoundException::fromHostingNotFound($hostsNotFound);
        }

        return $hosts;
    }
}
