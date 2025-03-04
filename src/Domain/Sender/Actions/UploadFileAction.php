<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Common\Adapters\Contracts\UuidGeneratorService;
use App\Domain\Common\Queue\Dispatcher;
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
    public const array ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png'];

    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly HostedFileRepository $fileHostingRepository,
        private readonly HostingRepository $hostingRepository,
        private readonly UuidGeneratorService $uuidGeneratorService,
        private readonly ZipFileService $zipFileService,
        private readonly SendFileToHostingJob $sendFileToHostingJob,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @return string[]
     */
    public function __invoke(UploadRequestData $uploadRequest): array
    {
        $this->validateUploadedFile($uploadRequest->uploadedFiles);

        $hostings = $this->queryHostingByIds($uploadRequest->hostingSlugs);

        if ($uploadRequest->shouldZip) {
            return [$this->zipFiles($uploadRequest->uploadedFiles, $hostings)];
        }

        return $this->sendIndividually($uploadRequest->uploadedFiles, $hostings);
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hostings
     */
    private function zipFiles(array $uploadedFiles, array $hostings): string
    {
        $uploadDir = __DIR__ . '/../../../../storage/uploads';

        $fileUuid = $this->uuidGeneratorService->generateUuid();
        $filename = $this->generateFilename($fileUuid);
        $filepath = $this->zipFileService->zipFiles($uploadedFiles, $uploadDir, $filename);
        $filesize = filesize($filepath);
        $mimeType = mime_content_type($filepath);

        $fileId = $this->fileRepository->create(
            new CreateFileData(
                $fileUuid,
                $filepath,
                $mimeType,
                $filesize,
            )
        );

        $encodedFile = new EncodedFileData(
            base64_encode(file_get_contents($filepath)),
            $filepath,
            $mimeType,
            $filesize,
        );

        $this->sendFileToHosting($fileId, $hostings, $encodedFile);

        return $fileUuid;
    }

    private function generateFilename(string $fileUuid): string
    {
        $date = (new DateTime('now'))->format('Y-m-d_H-i-s');

        return sprintf('upload_%s_%s.%s', $date, $fileUuid, 'zip');
    }

    /**
     * @param UploadedFileInterface[] $uploadedFiles
     * @param string[] $hostings
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

            $this->sendFileToHosting($fileId, $hostings, $encodedFile);
        }

        return $filesUuid;
    }

    /**
     * @param HostingData[] $hostings
     */
    private function sendFileToHosting(int $fileId, array $hostings, EncodedFileData $encodedFile): void
    {
        foreach ($hostings as $hosting) {
            $fileHostingId = $this->fileHostingRepository->create(
                new CreateHostedFileData(
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

            $this->dispatcher->addJob($this->sendFileToHostingJob)->dispatch();
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

            if (! in_array($uploadedFile->getClientMediaType(), self::ALLOWED_MIME_TYPES)) {
                throw new InvalidFileException(sprintf('Invalid file type, filename: %s', $filename));
            }
        });
    }

    /**
     * @param string[] $hostingSlugInPayload
     * @throws HostingNotFoundException
     * @return HostingData[]
     */
    private function queryHostingByIds(array $hostingSlugInPayload): array
    {
        $hostings = $this->hostingRepository->queryBySlugs($hostingSlugInPayload);
        $hostingsFound = array_column($hostings, 'slug');

        if ($hostingsNotFound = array_diff($hostingSlugInPayload, $hostingsFound)) {
            throw HostingNotFoundException::fromHostingNotFound($hostingsNotFound);
        }

        return $hostings;
    }
}
