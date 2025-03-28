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
        $this->logger->info(
            sprintf('[%s] Sending file to hosting', __METHOD__),
            [
                'hosting_slug' => $sendFileToHosting->hostingSlug,
                'hosted_file_id' => $sendFileToHosting->fileHostingId,
            ]
        );

        $this->fileHostingRepository->updateStatus(
            $sendFileToHosting->fileHostingId,
            FileHostingStatus::PROCESSING
        );

        $fileSenderService = $this->fileSenderFactory->create(
            $sendFileToHosting->hostingSlug
        );

        $this->logger->info(
            sprintf('[%s] File sender service class', __METHOD__),
            [
                'hosting_slug' => $sendFileToHosting->hostingSlug,
                'hosted_file_id' => $sendFileToHosting->fileHostingId,
                'file_sender_service_class' => $sendFileToHosting->hostingSlug,
            ]
        );

        try {
            $fileOnHosting = $fileSenderService->send($sendFileToHosting->encodedFile);
        } catch (FailedToSendFileException $exception) {
            $this->logger->error(
                sprintf('[%s] Failed to send file', __METHOD__),
                [
                    'hosting_slug' => $sendFileToHosting->hostingSlug,
                    'hosted_file_id' => $sendFileToHosting->fileHostingId,
                    'exception' => $exception,
                ]
            );

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

        $this->logger->info(
            sprintf('[%s] File sent to hosting', __METHOD__),
            [
                'hosting_slug' => $sendFileToHosting->hostingSlug,
                'hosted_file_id' => $sendFileToHosting->fileHostingId,
            ]
        );
    }
}
