<?php

declare(strict_types=1);

namespace App\Domain\Sender\Actions;

use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileSenderFactory;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\Enums\FileHostingStatus;
use App\Domain\Sender\Exceptions\FailedToSendFileException;
use Psr\Log\LoggerInterface;

class SendFileToHostingAction
{
    public function __construct(
        private readonly FileHostingRepository $fileHostingRepository,
        private readonly FileSenderFactory $fileSenderFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws FailedToSendFileException
     */
    public function __invoke(SendFileToHostingData $sendFileToHosting): void
    {
        $this->fileHostingRepository->updateStatus(
            $sendFileToHosting->fileHostingId,
            FileHostingStatus::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hostingSlug
        );

        try {
            $fileHosting = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (FailedToSendFileException $exception) {
            $this->logger->error(sprintf('[%s] Failed to send file to service', __METHOD__), [
                'hosting_slug' => $sendFileToHosting->hostingSlug,
                'hosted_file_id' => $sendFileToHosting->fileHostingId,
                'exception' => $exception,
            ]);

            $this->fileHostingRepository->updateStatus(
                $sendFileToHosting->fileHostingId,
                FileHostingStatus::SEND_FAILURE
            );

            throw $exception;
        }

        $this->fileHostingRepository->updateStatus(
            $sendFileToHosting->fileHostingId,
            FileHostingStatus::SEND_SUCCESS
        );
    }
}
