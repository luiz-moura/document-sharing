<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusOnHostEnum;
use App\Domain\Sender\Exceptions\FailedToUploadFileToHostingException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\Utils\Mocks\Sender\HostedFileDataFactory;
use Tests\Utils\Mocks\Sender\SendFileToHostingDataFactory;

class SendFileToHostingActionTest extends TestCase
{
    private MockObject|FileSenderFactory $fileSenderFactory;
    private MockObject|FileSenderService $fileSenderService;
    private MockObject|HostedFileRepository $fileHostingRepository;
    private MockObject|LoggerInterface $logger;
    private SendFileToHostingAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSenderFactory = $this->createMock(FileSenderFactory::class);
        $this->fileSenderService = $this->createMock(FileSenderService::class);
        $this->fileHostingRepository = $this->createMock(HostedFileRepository::class);
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

        $hostedFile = HostedFileDataFactory::create();
        $updateAccessLinkHostedFile = new UpdateAccessLinkHostedFileData(
            externalId: $hostedFile->fileId,
            webViewLink: $hostedFile->webViewLink,
            webContentLink: $hostedFile->webContentLink,
        );

        $this->fileHostingRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($sendFileToHosting->hostedFileId, FileStatusOnHostEnum::PROCESSING);

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hostingSlug)
            ->willReturn($this->fileSenderService);

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->encodedFile)
            ->willReturn($hostedFile);

        $this->fileHostingRepository
            ->expects($this->once())
            ->method('updateAccessLink')
            ->with(
                $sendFileToHosting->hostedFileId,
                $updateAccessLinkHostedFile
            );

        $this->sut->__invoke($sendFileToHosting);
    }

    public function testShouldFailWhenUploadTheFileToTheHosting(): void
    {
        $sendFileToHosting = SendFileToHostingDataFactory::create();

        $this->fileHostingRepository
            ->expects($this->exactly(2))
            ->method('updateStatus')
            ->with(
                $sendFileToHosting->hostedFileId,
                $this->logicalOr(
                    FileStatusOnHostEnum::PROCESSING,
                    FileStatusOnHostEnum::SEND_FAILURE
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

        $this->expectException(FailedToUploadFileToHostingException::class);
        $this->expectExceptionMessage('Failed to upload file to hosting.');

        $this->sut->__invoke($sendFileToHosting);
    }
}
