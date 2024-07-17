<?php

namespace Tests\Domain\Sender;

use App\Domain\Sender\Actions\UploadFileAction;
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
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileActionTest extends TestCase
{
    private $fileRepository;
    private $fileHostingRepository;
    private $hostingRepository;
    private $sendFileToHostingJob;
    private UploadFileAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->createMock(FileRepository::class);
        $this->fileHostingRepository = $this->createMock(FileHostingRepository::class);
        $this->hostingRepository = $this->createMock(HostingRepository::class);
        $this->sendFileToHostingJob = $this->createMock(SendFileToHostingJob::class);

        $this->sut = new UploadFileAction(
            $this->fileRepository,
            $this->fileHostingRepository,
            $this->hostingRepository,
            $this->sendFileToHostingJob,
        );
    }

    public function testShouldUploadTheFileSuccessfully()
    {
        $fileId = 900;
        $hostingIds = [100, 200];
        $googleDriveHosting = new HostingData($hostingIds[0], 'Google Drive');
        $dropboxHosting = new HostingData($hostingIds[1], 'Dropbox');

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->method('getClientFilename')->willReturn('example.png');
        $uploadedFile->method('getSize')->willReturn(300);
        $uploadedFile->method('getClientMediaType')->willReturn('image/png');

        $this->fileRepository
            ->expects($this->once())
            ->method('create')
            ->with(
                new CreateFileData(
                    name: 'example.png',
                    size: 300,
                    mimeType: 'image/png'
                )
            )
            ->willReturn($fileId);

        $this->hostingRepository
            ->expects($this->once())
            ->method('queryByIds')
            ->with($hostingIds)
            ->willReturn([$googleDriveHosting, $dropboxHosting]);

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
            )->willReturnOnConsecutiveCalls(1, 2);

        $this->sendFileToHostingJob
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                $this->logicalOr(
                    new SendFileToHostingData(
                        $uploadedFile,
                        $googleDriveHosting
                    ),
                    new SendFileToHostingData(
                        $uploadedFile,
                        $dropboxHosting
                    ),
                )
            );

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingIds,
                $uploadedFile
            )
        );
    }

    public function testShouldFailWhenTheFileIsInError()
    {
        $hostingIds = [100, 200];

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->method('getError')->willReturn(UPLOAD_ERR_CANT_WRITE);

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Failed to write file to disk.');

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingIds,
                $uploadedFile
            )
        );
    }

    public function testShouldFailWhenOneOrMoreHostingIsNotFound()
    {
        $hostingIds = [100, 200];

        $uploadedFile = $this->createMock(UploadedFileInterface::class);

        $this->hostingRepository
            ->expects($this->once())
            ->method('queryByIds')
            ->with($hostingIds)
            ->willReturn([]);

        $this->fileRepository
            ->expects($this->never())
            ->method('create');

        $this->fileHostingRepository
            ->expects($this->never())
            ->method('create');

        $this->sendFileToHostingJob
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(HostingNotFoundException::class);

        $this->sut->__invoke(
            new UploadRequestData(
                $hostingIds,
                $uploadedFile
            )
        );
    }
}
