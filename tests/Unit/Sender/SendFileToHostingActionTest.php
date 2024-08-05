<?php

declare(strict_types=1);

namespace Tests\Unit\Sender;

use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\UpdateAccessLinkFileHostingData;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Mocks\Sender\HostedFileDataFactory;
use Tests\Utils\Mocks\Sender\SendFileToHostingDataFactory;

class SendFileToHostingActionTest extends TestCase
{
    private $fileSenderFactory;
    private $fileSenderService;
    private $fileHostingRepository;
    private SendFileToHostingAction $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSenderFactory = $this->createMock(FileSenderFactory::class);
        $this->fileSenderService = $this->createMock(FileSenderService::class);
        $this->fileHostingRepository = $this->createMock(FileHostingRepository::class);

        $this->sut = new SendFileToHostingAction(
            $this->fileSenderFactory,
            $this->fileHostingRepository,
        );
    }

    public function testShouldUploadTheFileToTheHostingSuccessfully()
    {
        $sendFileToHosting = SendFileToHostingDataFactory::create();

        $hostedFile = HostedFileDataFactory::create();
        $updateAccessLinkFileHosting = new UpdateAccessLinkFileHostingData(
            externalFileId: $hostedFile->fileId,
            webViewLink: $hostedFile->webViewLink,
            webContentLink: $hostedFile->webContentLink,
        );

        $this->fileSenderService
            ->expects($this->once())
            ->method('send')
            ->with($sendFileToHosting->uploadedFile)
            ->willReturn($hostedFile);

        $this->fileSenderFactory
            ->expects($this->once())
            ->method('create')
            ->with($sendFileToHosting->hosting->name)
            ->willReturn($this->fileSenderService);

        $this->fileHostingRepository
            ->expects($this->once())
            ->method('updateAccessLink')
            ->with(
                $sendFileToHosting->fileHostingId,
                $updateAccessLinkFileHosting
            );

        $this->sut->__invoke($sendFileToHosting);
    }
}