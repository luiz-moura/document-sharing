<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostedFileData;
use App\Infrastructure\Integrations\Hosting\Common\Traits\GenerateFilename;
use Psr\Log\LoggerInterface;
use Spatie\Dropbox\Client as DropboxClient;
use Throwable;

class DropboxService implements FileSenderService
{
    use GenerateFilename;

    private readonly DropboxClient $client;

    public function __construct(
        private readonly DropboxTokenProvider $tokenProvider,
        private readonly LoggerInterface $logger,
    ) {
        $this->client = new DropboxClient($this->tokenProvider);
    }

    public function send(EncodedFileData $encodedFile): HostedFileData
    {
        $this->logger->info(sprintf('[%s] Uploading file to Dropbox.', __METHOD__), [
            'filename' => $encodedFile->filename,
            'mime_type' => $encodedFile->mimeType,
            'size' => $encodedFile->size,
        ]);

        $filename = $this->generateFilename($encodedFile->filename);

        try {
            /**
             * @var array{path_display: string, id: string, name: string, size: string, path_lower: string} $uploadedFile
             */
            $uploadedFile = $this->client->upload($filename, base64_decode($encodedFile->base64), 'add');

            /**
             * @var array{url: string, name: string, size: string, path_lower: string} $sharedLink
             */
            $sharedLink = $this->client->createSharedLinkWithSettings($uploadedFile['path_display']);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[%s] Failed to upload file to Dropbox.', __METHOD__), [
                'file' => $encodedFile->filename,
                'mime_type' => $encodedFile->mimeType,
                'size' => $encodedFile->size,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        return new HostedFileData(
            fileId: strval($uploadedFile['id']),
            filename: $uploadedFile['name'],
            webViewLink: $sharedLink['url'],
            webContentLink: str_replace('?dl=0', '?dl=1', $sharedLink['url'])
        );
    }
}
