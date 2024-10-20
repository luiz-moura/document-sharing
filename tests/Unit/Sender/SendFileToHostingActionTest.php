<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusEnum;
use App\Domain\Sender\Exceptions\FailedToUploadFileToHostingException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\Utils\Mocks\Sender\HostedFileDataFactory;
use Tests\Utils\Mocks\Sender\SendFileToHostingDataFactory;

class SendFileToHostingActionTest extends TestCase
{
    private $fileSenderFactory;
    private $fileSenderService;
    private $hostedFileRepository;
    private $logger;
    private SendFileToHostingAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FileSenderFactory */
        $this->fileSenderFactory = $this->createMock(FileSenderFactory::class);
        $this->fileSenderService = $this->createMock(FileSenderService::class);
        /** @var HostedFileRepository */
        $this->hostedFileRepository = $this->createMock(HostedFileRepository::class);
        /** @var LoggerInterface */
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new SendFileToHostingAction(
            $this->hostedFileRepository,
            $this->fileSenderFactory,
            $this->logger,
        );
    }

    public function testShouldUploadTheFileToTheHostingSuccessfully(): void
    {
        $sendFileToHosting = SendFileToHostingDataFactory::create();

        $hostedFile = HostedFileDataFactory::create();
        $updateAccessLinkHostedFile = new UpdateAccessLinkHostedFileData(
            externalFileId: $hostedFile->fileId,
            webViewLink: $hostedFile->webViewLink,
            webContentLink: $hostedFile->webContentLink,
        );

        $this->hostedFileRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($sendFileToHosting->hostedFileId, FileStatusEnum::PROCESSING);

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hosting->slug)
            ->willReturn($this->fileSenderService);

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->encodedFile)
            ->willReturn($hostedFile);

        $this->hostedFileRepository
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

        $this->hostedFileRepository
            ->expects($this->exactly(2))
            ->method('updateStatus')
            ->with(
                $sendFileToHosting->hostedFileId,
                $this->logicalOr(
                    FileStatusEnum::PROCESSING,
                    FileStatusEnum::SEND_FAILURE
                )
            );

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hosting->slug)
            ->willReturn($this->fileSenderService);

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->encodedFile)
            ->willThrowException(new \Exception('Failed to upload the file'));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(FailedToUploadFileToHostingException::class);
        $this->expectExceptionMessage('Failed to upload file to hosting.');

        $this->sut->__invoke($sendFileToHosting);
    }
}
