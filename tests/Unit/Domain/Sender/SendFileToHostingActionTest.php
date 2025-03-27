<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\Enums\FileHostingStatus;
use App\Domain\Sender\Exceptions\FailedToSendFileException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\Utils\Mocks\Sender\FileOnHostingDataFactory;
use Tests\Utils\Mocks\Sender\SendFileToHostingDataFactory;

class SendFileToHostingActionTest extends TestCase
{
    private MockObject|FileSenderFactory $fileSenderFactory;
    private MockObject|FileSenderService $fileSenderService;
    private MockObject|FileHostingRepository $fileHostingRepository;
    private MockObject|LoggerInterface $logger;
    private SendFileToHostingAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSenderFactory = $this->createMock(FileSenderFactory::class);
        $this->fileSenderService = $this->createMock(FileSenderService::class);
        $this->fileHostingRepository = $this->createMock(FileHostingRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new SendFileToHostingAction(
            $this->fileHostingRepository,
            $this->fileSenderFactory,
            $this->logger,
        );
    }

    public function testShouldUploadTheFileToTheHostingSuccessfully(): void
    {
        $sendFileToHosting = SendFileToHostingDataFactory::create();

        $fileOnHosting = FileOnHostingDataFactory::create();

        $this->fileHostingRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($sendFileToHosting->fileHostingId, FileHostingStatus::PROCESSING);

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hostingSlug)
            ->willReturn($this->fileSenderService);

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->encodedFile)
            ->willReturn($fileOnHosting);

        $this->sut->__invoke($sendFileToHosting);
    }

    public function testShouldFailWhenUploadTheFileToTheHosting(): void
    {
        $sendFileToHosting = SendFileToHostingDataFactory::create();

        $this->fileHostingRepository
            ->expects($this->exactly(2))
            ->method('updateStatus')
            ->with(
                $sendFileToHosting->fileHostingId,
                $this->logicalOr(
                    FileHostingStatus::PROCESSING,
                    FileHostingStatus::SEND_FAILURE
                )
            );

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hostingSlug)
            ->willReturn($this->fileSenderService);

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->encodedFile)
            ->willThrowException(new Exception('Failed to upload the file'));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(FailedToSendFileException::class);
        $this->expectExceptionMessage('Failed to upload file to hosting.');

        $this->sut->__invoke($sendFileToHosting);
    }
}
