<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Common\Services\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Common\Queue\JobDispatcher;
use App\Domain\Common\Services\ZipArchive\ZipArchiveService;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadFileData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileUseCase
{
    public const string APPLICATION_ZIP_MIME_TYPE = 'application/zip';

    public const string APPLICATION_ZIP_EXTENSION = 'zip';

    public function __construct(
        private readonly ValidateUploadedFileAction $validateUploadedFileAction,
        private readonly GenerateFilenameAction $generateFilenameAction,
        private readonly FileRepository $fileRepository,
        private readonly FileHostingRepository $fileHostingRepository,
        private readonly HostingRepository $hostingRepository,
        private readonly UuidGeneratorService $uuidGeneratorService,
        private readonly ZipArchiveService $zipArchiveService,
        private readonly SendFileToHostingJob $sendFileToHostingJob,
        private readonly JobDispatcher $jobDispatcher,
    ) {
    }

    /**
     * @return string[]
     */
    public function __invoke(UploadFileData $uploadFileData): array
    {
        foreach ($uploadFileData->uploadedFiles as $uploadedFile) {
            ($this->validateUploadedFileAction)($uploadedFile);
        }

        $hostings = $this->queryHostingByIds($uploadFileData->hostingSlugs);

        if ($uploadFileData->shouldZip) {
            return [$this->sendZippedArchive($uploadFileData->uploadedFiles, $hostings)];
        }

        return $this->sendIndividually($uploadFileData->uploadedFiles, $hostings);
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hostings
     */
    private function sendZippedArchive(array $uploadedFiles, array $hostings): string
    {
        $fileUuid = $this->uuidGeneratorService->generateUuid();
        $filename = ($this->generateFilenameAction)($fileUuid, self::APPLICATION_ZIP_EXTENSION);

        $binary = $this->zipArchiveService->zipArchive($uploadedFiles);
        $filesize = strlen($binary);
        $mimeType = self::APPLICATION_ZIP_MIME_TYPE;

        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $fileUuid,
                $filename,
                $mimeType,
                $filesize,
            )
        );

        $encodedFile = new EncodedFileData(
            base64_encode($binary),
            $filename,
            $mimeType,
            $filesize,
        );

        $this->sendFileToHosting($hostings, $fileId, $encodedFile);

        return $fileUuid;
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hostings
     *
     * @return string[]
     */
    private function sendIndividually(array $uploadedFiles, array $hostings): array
    {
        $filesUuid = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $fileUuid = $this->uuidGeneratorService->generateUuid();
            $filesUuid[] = $fileUuid;

            $fileId = $this->fileRepository->create(
                new CreateFileData(
                    $fileUuid,
                    $uploadedFile->getClientFilename(),
                    $uploadedFile->getClientMediaType(),
                    $uploadedFile->getSize(),
                )
            );

            $stream = $uploadedFile->getStream();
            $stream->rewind();

            $encodedFile = new EncodedFileData(
                base64_encode($stream->getContents()),
                $uploadedFile->getClientFilename(),
                $uploadedFile->getClientMediaType(),
                $uploadedFile->getSize(),
            );

            $this->sendFileToHosting($hostings, $fileId, $encodedFile);
        }

        return $filesUuid;
    }

    /**
     * @param HostingData[] $hostings
     */
    private function sendFileToHosting(array $hostings, int $fileId, EncodedFileData $encodedFile): void
    {
        foreach ($hostings as $hosting) {
            $fileHostingId = $this->fileHostingRepository->create(
                new CreateFileHostingData(
                    $fileId,
                    $hosting->id,
                )
            );

            $this->sendFileToHostingJob->setArgs(
                new SendFileToHostingData(
                    $hosting->slug,
                    $fileHostingId,
                    $encodedFile,
                )
            );

            $this->jobDispatcher->addJob($this->sendFileToHostingJob)->dispatch();
        }
    }

    /**
     * @param string[] $hostingSlugInPayload
     * @throws HostingNotFoundException
     *
     * @return HostingData[]
     */
    private function queryHostingByIds(array $hostingSlugInPayload): array
    {
        $hostings = $this->hostingRepository->queryBySlugs($hostingSlugInPayload);

        if (count($hostings) !== count($hostingSlugInPayload)) {
            $hostingsFound = array_column($hostings, 'slug');
            $hostingsNotFound = array_diff($hostingSlugInPayload, $hostingsFound);

            throw new HostingNotFoundException(
                implode(', ', $hostingsNotFound)
            );
        }

        return $hostings;
    }
}
