<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Common\Adapters\Contracts\UuidGeneratorService;
use App\Domain\Sender\Actions\UploadFileAction;
use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadRequestData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Exceptions\InvalidFileException;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use App\Domain\Sender\Services\ZipFile\ZipFileService;
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
    private MockObject|FileRepository $fileRepository;
    private MockObject|HostedFileRepository $hostedFileRepository;
    private MockObject|HostingRepository $hostingRepository;
    private MockObject|SendFileToHostingJob $sendFileToHostingJob;
    private MockObject|UuidGeneratorService $uuidGeneratorService;
    private MockObject|ZipFileService $zipFileService;
    private UploadFileAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = faker();

        $this->fileRepository = $this->createMock(FileRepository::class);
        $this->hostedFileRepository = $this->createMock(HostedFileRepository::class);
        $this->hostingRepository = $this->createMock(HostingRepository::class);
        $this->sendFileToHostingJob = $this->createMock(SendFileToHostingJob::class);
        $this->uuidGeneratorService = $this->createMock(UuidGeneratorService::class);
        $this->zipFileService = $this->createMock(ZipFileService::class);

        $this->sut = new UploadFileAction(
            $this->fileRepository,
            $this->hostedFileRepository,
            $this->hostingRepository,
            $this->sendFileToHostingJob,
            $this->uuidGeneratorService,
            $this->zipFileService,
        );
    }

    public function testShouldFailWhenTheFileIsInError(): void
    {
        $hostingSlug = [$this->faker->randomDigitNotZero(), $this->faker->slug(1)];
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

        $this->hostedFileRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlug,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenTheFileSizeIsGreatestThanAllowed(): void
    {
        $fileSize = 6 * 1024 * 1024; // 6MB
        $fileType = 'image/png';
        $uploadedFile = UploadedFileFactory::create([
            'size' => $fileSize,
            'type' => $fileType
        ]);

        $hostingSlug = [$this->faker->randomDigitNotZero(), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs');

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->hostedFileRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('File size is too large');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlug,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenTheFileTypeIsNotAllowed(): void
    {
        $fileSize = 5 * 1024 * 1024; // 5MB
        $fileType = 'image/gif';
        $uploadedFile = UploadedFileFactory::create([
            'size' => $fileSize,
            'type' => $fileType
        ]);

        $hostingSlug = [$this->faker->randomDigitNotZero(), $this->faker->slug(1)];

        $this->hostingRepository
            ->expects($this->never())
            ->method('queryBySlugs');

        $this->uuidGeneratorService
            ->expects($this->never())
            ->method('generateUuid');

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->hostedFileRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Invalid file type');

        $this->sut->__invoke(
            new UploadRequestData(
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

        $this->hostedFileRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Invalid file content');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldFailWhenOneOrMoreHostingIsNotFound(): void
    {
        $fileSize = 5 * 1024 * 1024; // 5MB
        $fileType = 'image/jpeg';

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->once())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);
        $uploadedFile->expects($this->exactly(2))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->once())
            ->method('getClientMediaType')
            ->willReturn($fileType);
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

        $this->hostedFileRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->expectException(HostingNotFoundException::class);

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }

    public function testShouldUploadTheFileSuccessfully(): void
    {
        $fileSize = 5 * 1024 * 1024; // 5MB
        $fileType = 'image/jpeg';
        $fileName = $this->faker->filePath() . '.' . $this->faker->fileExtension();
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
            ->willReturn($fileName);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientMediaType')
            ->willReturn($fileType);
        $uploadedFile->expects($this->exactly(4))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->exactly(1))
            ->method('getStream')
            ->willReturn($stream);

        $createFile = CreateFileDataFactory::create([
            'name' => $fileName,
            'size' => $fileSize,
            'mimeType' => $fileType,
        ]);

        $fileId = $this->faker->randomDigitNotZero();
        $googleDriveHosting = new HostingData(1, 'google-drive', 'Google Drive');
        $dropboxHosting = new HostingData(2, 'Dropbox', 'dropbox');
        $hostingSlugs = [$googleDriveHosting->slug, $dropboxHosting->slug];
        $hostedFileIds = [$this->faker->randomDigitNotZero(), $this->faker->randomDigitNotZero()];

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

        $this->zipFileService
            ->expects($this->never())
            ->method('zipFiles');

        $this->hostedFileRepository
            ->expects($this->exactly(2))
            ->method('create')
            ->with(
                $this->logicalOr(
                    new CreateHostedFileData($fileId, $googleDriveHosting->id),
                    new CreateHostedFileData($fileId, $dropboxHosting->id),
                )
            )->willReturnOnConsecutiveCalls(
                $hostedFileIds[0],
                $hostedFileIds[1]
            );

        $this->sendFileToHostingJob
            ->expects($this->exactly(2))
            ->method('setArgs')
            ->with(
                $this->logicalOr(
                    new SendFileToHostingData(
                        $googleDriveHosting->slug,
                        $hostedFileIds[0],
                        new EncodedFileData(
                            $fileName,
                            $fileType,
                            $fileSize,
                            base64_encode($streamContent),
                        ),
                    ),
                    new SendFileToHostingData(
                        $dropboxHosting->slug,
                        $hostedFileIds[1],
                        new EncodedFileData(
                            $fileName,
                            $fileType,
                            $fileSize,
                            base64_encode($streamContent),
                        ),
                    ),
                )
            )->willReturnSelf();

        $this->sendFileToHostingJob
            ->expects($this->exactly(2))
            ->method('dispatch');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                [$uploadedFile],
                shouldZip: false,
            )
        );
    }
}
