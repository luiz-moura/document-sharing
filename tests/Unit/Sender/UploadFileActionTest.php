<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Common\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Actions\UploadFileAction;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\DTOs\UploadRequestData;
use App\Domain\Sender\Exceptions\HostingNotFoundException;
use App\Domain\Sender\Exceptions\InvalidFileException;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Utils\Mocks\Sender\CreateFileDataFactory;
use Tests\Utils\Mocks\Sender\UploadedFileFactory;
use function Tests\Utils\Faker\faker;

class UploadFileActionTest extends TestCase
{
    private Generator $faker;
    private $fileRepository;
    private $fileHostingRepository;
    private $hostingRepository;
    private $sendFileToHostingAction;
    private $uuidGeneratorService;
    private UploadFileAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = faker();

        /** @var FileRepository */
        $this->fileRepository = $this->createMock(FileRepository::class);
        /** @var FileHostingRepository */
        $this->fileHostingRepository = $this->createMock(FileHostingRepository::class);
        /** @var HostingRepository */
        $this->hostingRepository = $this->createMock(HostingRepository::class);
        /** @var SendFileToHostingAction */
        $this->sendFileToHostingAction = $this->createMock(SendFileToHostingAction::class);
        /** @var UuidGeneratorService */
        $this->uuidGeneratorService = $this->createMock(UuidGeneratorService::class);

        $this->sut = new UploadFileAction(
            $this->fileRepository,
            $this->fileHostingRepository,
            $this->hostingRepository,
            $this->sendFileToHostingAction,
            $this->uuidGeneratorService,
        );
    }

    public function testShouldFailWhenTheFileIsInError()
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

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingAction
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlug,
                $uploadedFile
            )
        );
    }

    public function testShouldFailWhenOneOrMoreHostingIsNotFound()
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

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingAction
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(HostingNotFoundException::class);

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                $uploadedFile
            )
        );
    }

    public function testShouldUploadTheFileSuccessfully()
    {
        $fileId = $this->faker->randomDigitNotZero();
        $uploadedFile = UploadedFileFactory::create();
        $createFile = CreateFileDataFactory::fromUploadedFile($uploadedFile);

        $hostingSlugs = [$this->faker->slug(1), $this->faker->slug(1)];
        $googleDriveHosting = new HostingData(1, 'Google Drive', 'google-drive');
        $dropboxHosting = new HostingData(2, 'Dropbox', 'dropbox');

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

        $this->fileHostingRepository
            ->expects($this->exactly(2))
            ->method('create')
            ->with(
                $this->logicalOr(
                    new CreateFileHostingData(
                        fileId: $fileId,
                        hosting: $googleDriveHosting
                    ),
                    new CreateFileHostingData(
                        fileId: $fileId,
                        hosting: $dropboxHosting
                    ),
                )
            )->willReturnOnConsecutiveCalls(
                $fileHostingIds[0],
                $fileHostingIds[1]
            );

        $this->sendFileToHostingAction
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->with(
                $this->logicalOr(
                    new SendFileToHostingData(
                        $fileHostingIds[0],
                        $googleDriveHosting,
                        $uploadedFile,
                    ),
                    new SendFileToHostingData(
                        $fileHostingIds[1],
                        $dropboxHosting,
                        $uploadedFile,
                    ),
                )
            );

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingSlugs,
                $uploadedFile
            )
        );
    }
}
