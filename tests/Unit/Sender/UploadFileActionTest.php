<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Common\Uuid\Contracts\UuidGeneratorService;
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
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Utils\Mocks\Sender\CreateFileDataFactory;
use Tests\Utils\Mocks\Sender\UploadedFileFactory;

use function Tests\Utils\Faker\faker;

class UploadFileActionTest extends TestCase
{
    private Generator $faker;
    private $fileRepository;
    private $hostedFileRepository;
    private $hostingRepository;
    private $sendFileToHostingJob;
    private $uuidGeneratorService;
    private UploadFileAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = faker();

        /** @var FileRepository */
        $this->fileRepository = $this->createMock(FileRepository::class);
        /** @var HostedFileRepository */
        $this->hostedFileRepository = $this->createMock(HostedFileRepository::class);
        /** @var HostingRepository */
        $this->hostingRepository = $this->createMock(HostingRepository::class);
        /** @var SendFileToHostingJob */
        $this->sendFileToHostingJob = $this->createMock(SendFileToHostingJob::class);
        /** @var UuidGeneratorService */
        $this->uuidGeneratorService = $this->createMock(UuidGeneratorService::class);

        $this->sut = new UploadFileAction(
            $this->fileRepository,
            $this->hostedFileRepository,
            $this->hostingRepository,
            $this->sendFileToHostingJob,
            $this->uuidGeneratorService,
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

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlug,
                $uploadedFile
            )
        );
    }

    public function testShouldFailWhenOneOrMoreHostingIsNotFound(): void
    {
        $hostingSlugs = [$this->faker->slug(1), $this->faker->slug(1)];
        /** @var UploadedFileInterface */
        $uploadedFile = $this->createMock(UploadedFileInterface::class);

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

        $this->expectException(HostingNotFoundException::class);

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                $uploadedFile
            )
        );
    }

    public function testShouldUploadTheFileSuccessfully(): void
    {
        $fileId = $this->faker->randomDigitNotZero();

        $hostingSlugs = [$this->faker->slug(1), $this->faker->slug(1)];
        $googleDriveHosting = new HostingData(1, 'Google Drive', 'google-drive');
        $dropboxHosting = new HostingData(2, 'Dropbox', 'dropbox');

        $hostedFileIds = [$this->faker->randomDigitNotZero(), $this->faker->randomDigitNotZero()];
        $uploadedFile = UploadedFileFactory::create();
        $createFile = CreateFileDataFactory::fromUploadedFile($uploadedFile);

        $streamContent = 'any';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(2))
            ->method('__toString')
            ->willReturn($streamContent);

        $uploadFile = $this->createMock(UploadedFileInterface::class);

        $uploadFile->expects($this->exactly(3))
            ->method('getClientFilename')
            ->willReturn($uploadedFile->getClientFilename());

        $uploadFile->expects($this->exactly(3))
            ->method('getClientMediaType')
            ->willReturn($uploadedFile->getClientMediaType());

        $uploadFile->expects($this->exactly(3))
            ->method('getSize')
            ->willReturn($uploadedFile->getSize());

        $uploadFile->expects($this->exactly(2))
            ->method('getStream')
            ->willReturn($stream);

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

        $this->hostedFileRepository
            ->expects($this->exactly(2))
            ->method('create')
            ->with(
                $this->logicalOr(
                    new CreateHostedFileData(
                        fileId: $fileId,
                        hosting: $googleDriveHosting
                    ),
                    new CreateHostedFileData(
                        fileId: $fileId,
                        hosting: $dropboxHosting
                    ),
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
                        $googleDriveHosting,
                        $hostedFileIds[0],
                        new EncodedFileData(
                            filename: $uploadedFile->getClientFilename(),
                            mediaType: $uploadedFile->getClientMediaType(),
                            size: $uploadedFile->getSize(),
                            base64: $streamContent,
                        ),
                    ),
                    new SendFileToHostingData(
                        $dropboxHosting,
                        $hostedFileIds[1],
                        new EncodedFileData(
                            filename: $uploadedFile->getClientFilename(),
                            mediaType: $uploadedFile->getClientMediaType(),
                            size: $uploadedFile->getSize(),
                            base64: $streamContent,
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
                $uploadFile
            )
        );
    }
}
