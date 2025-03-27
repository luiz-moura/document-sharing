<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Common\Services\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Common\Queue\JobDispatcher;
use App\Domain\Common\Services\ZipArchive\ZipArchiveService;
use App\Domain\Sender\Actions\GenerateFilenameAction;
use App\Domain\Sender\Actions\UploadFileUseCase;
use App\Domain\Sender\Actions\ValidateUploadedFileAction;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadFileData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Exceptions\InvalidUploadedFileException;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Utils\Mocks\Sender\CreateFileDataFactory;
use Tests\Utils\Mocks\Sender\UploadedFileFactory;

use function Tests\Utils\Faker\faker;

class UploadFileActionTest extends TestCase
{
    private Generator $faker;
    private MockObject|ValidateUploadedFileAction $validateUploadedFileAction;
    private MockObject|GenerateFilenameAction $generateFilenameAction;
    private MockObject|FileRepository $fileRepository;
    private MockObject|FileHostingRepository $fileHostingRepository;
    private MockObject|HostingRepository $hostingRepository;
    private MockObject|UuidGeneratorService $uuidGeneratorService;
    private MockObject|ZipArchiveService $zipArchiveService;
    private MockObject|SendFileToHostingJob $sendFileToHostingJob;
    private MockObject|JobDispatcher $jobDispatcher;
    private UploadFileUseCase $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = faker();

        $this->validateUploadedFileAction = $this->createMock(ValidateUploadedFileAction::class);
        $this->generateFilenameAction = $this->createMock(GenerateFilenameAction::class);
        $this->fileRepository = $this->createMock(FileRepository::class);
        $this->fileHostingRepository = $this->createMock(FileHostingRepository::class);
        $this->hostingRepository = $this->createMock(HostingRepository::class);
        $this->uuidGeneratorService = $this->createMock(UuidGeneratorService::class);
        $this->zipArchiveService = $this->createMock(ZipArchiveService::class);
        $this->sendFileToHostingJob = $this->createMock(SendFileToHostingJob::class);
        $this->jobDispatcher = $this->createMock(JobDispatcher::class);

        $this->sut = new UploadFileUseCase(
            $this->validateUploadedFileAction,
            $this->generateFilenameAction,
            $this->fileRepository,
            $this->fileHostingRepository,
            $this->hostingRepository,
            $this->uuidGeneratorService,
            $this->zipArchiveService,
            $this->sendFileToHostingJob,
            $this->jobDispatcher,
        );
    }

    public function testShouldFailWhenTheFileIsInError(): void
    {
        $hostingSlug = [$this->faker->slug(1), $this->faker->slug(1)];
        $uploadedFile = UploadedFileFactory::create(['error' => UPLOAD_ERR_CANT_WRITE]);

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs');

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('setArgs');

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->jobDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(InvalidUploadedFileException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlug,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenTheFileSizeIsGreatestThanAllowed(): void
    {
        $fileSize = 6 * 1024 * 1024; // 6MB
        $mimeType = 'image/png';
        $uploadedFile = UploadedFileFactory::create([
            'size' => $fileSize,
            'type' => $mimeType
        ]);

        $hostingSlug = [$this->faker->slug(1), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs');

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('setArgs');

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->jobDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(InvalidUploadedFileException::class);
        $this->expectExceptionMessage('File size is too large');

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlug,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenTheFileTypeIsNotAllowed(): void
    {
        $fileSize = 5 * 1024 * 1024; // 5MB
        $mimeType = 'image/gif';
        $uploadedFile = UploadedFileFactory::create([
            'size' => $fileSize,
            'type' => $mimeType
        ]);

        $hostingSlug = [$this->faker->slug(1), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs');

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('setArgs');

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->jobDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(InvalidUploadedFileException::class);
        $this->expectExceptionMessage('Invalid file type');

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlug,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenFileIsEmpty(): void
    {
        $fileSize = 0;

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->once())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);
        $uploadedFile->expects($this->once())
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->never())
            ->method('getClientMediaType');
        $uploadedFile->expects($this->never())
            ->method('getStream');

        $hostingSlugs = [$this->faker->slug(1), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs')
            ->with($hostingSlugs)
            ->willReturn([]);

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('setArgs');

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->jobDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(InvalidUploadedFileException::class);
        $this->expectExceptionMessage('Invalid file content');

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenOneOrMoreHostingIsNotFound(): void
    {
        $fileSize = 5 * 1024 * 1024; // 5MB
        $mimeType = 'image/jpeg';

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->once())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);
        $uploadedFile->expects($this->exactly(2))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->once())
            ->method('getClientMediaType')
            ->willReturn($mimeType);
        $uploadedFile->expects($this->never())
            ->method('getStream');

        $hostingSlugs = [$this->faker->slug(1), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->once())
            ->method('queryBySlugs')
            ->with($hostingSlugs)
            ->willReturn([]);

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('setArgs');

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->jobDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(HostingNotFoundException::class);

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldUploadTheFileSuccessfully(): void
    {
        $filename = $this->faker->filePath() . '.' . $this->faker->fileExtension();
        $mimeType = 'image/jpeg';
        $fileSize = 5 * 1024 * 1024; // 5MB
        $streamContent = 'any';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(1))
            ->method('rewind');
        $stream->expects($this->exactly(1))
            ->method('getContents')
            ->willReturn($streamContent);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientFilename')
            ->willReturn($filename);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientMediaType')
            ->willReturn($mimeType);
        $uploadedFile->expects($this->exactly(4))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->exactly(1))
            ->method('getStream')
            ->willReturn($stream);

        $createFile = CreateFileDataFactory::create([
            'name' => $filename,
            'size' => $fileSize,
            'mimeType' => $mimeType,
        ]);

        $fileId = $this->faker->randomDigitNotZero();
        $googleDriveHosting = new HostingData(1, 'google-drive', 'Google Drive', null, null);
        $dropboxHosting = new HostingData(2, 'Dropbox', 'dropbox', null, null);
        $hostingSlugs = [$googleDriveHosting->slug, $dropboxHosting->slug];
        $fileHostingIds = [$this->faker->randomDigitNotZero(), $this->faker->randomDigitNotZero()];

        $this->hostingRepository
            ->expects($this->once())
            ->method('queryBySlugs')
            ->with($hostingSlugs)
            ->willReturn([$googleDriveHosting, $dropboxHosting]);

        $this->uuidGeneratorService
            ->expects($this->once())
            ->method('generateUuid')
            ->willReturn($createFile->uuid);

        $this->fileRepository
            ->expects($this->once())
            ->method('create')
            ->with($createFile)
            ->willReturn($fileId);

        $this->zipArchiveService
            ->expects($this->never())
            ->method('zipArchive');

        $this->fileHostingRepository
            ->expects($this->exactly(2))
            ->method('create')
            ->with(
                $this->logicalOr(
                    new CreateFileHostingData($fileId, $googleDriveHosting->id),
                    new CreateFileHostingData($fileId, $dropboxHosting->id),
                )
            )->willReturnOnConsecutiveCalls(
                $fileHostingIds[0],
                $fileHostingIds[1]
            );

        $this->sendFileToHostingJob
            ->expects($this->exactly(2))
            ->method('setArgs')
            ->with(
                $this->logicalOr(
                    new SendFileToHostingData(
                        $googleDriveHosting->slug,
                        $fileHostingIds[0],
                        new EncodedFileData(
                            base64_encode($streamContent),
                            $filename,
                            $mimeType,
                            $fileSize,
                        ),
                    ),
                    new SendFileToHostingData(
                        $dropboxHosting->slug,
                        $fileHostingIds[1],
                        new EncodedFileData(
                            base64_encode($streamContent),
                            $filename,
                            $mimeType,
                            $fileSize,
                        ),
                    ),
                )
            )->willReturnSelf();

        $this->jobDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch');

        $this->sut->__invoke(
            new UploadFileData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }
}
