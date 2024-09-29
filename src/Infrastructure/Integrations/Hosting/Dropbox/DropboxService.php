<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Log\LoggerInterface;
use Spatie\Dropbox\Client as DropboxClient;
use Throwable;

class DropboxService implements FileSenderService
{
    private DropboxClient $client;

    public function __construct(
        private LoggerInterface $logger
    ) {
        $dropboxToken = config('dropbox.token');
        $this->client = new DropboxClient($dropboxToken);
    }

    public function send(EncodedFileData $encodedFile): HostedFileData
    {
        $this->logger->info(sprintf('[%s] Uploading file to Dropbox.', __METHOD__), [
            'file' => $encodedFile->filename,
            'mediaType' => $encodedFile->mediaType,
            'size' => $encodedFile->size,
        ]);

        $fileName = $this->generateFileName($encodedFile->filename);

        try {
            /**
             * @var array{path_display: string, id: string, name: string, size: string, path_lower: string} $uploadedFile
             */
            $uploadedFile = $this->client->upload($fileName, $encodedFile->base64, 'add');
            /**
             * @var array{url: string, name: string, size: string, path_lower: string} $sharedLink
             */
            $sharedLink = $this->client->createSharedLinkWithSettings($uploadedFile['path_display']);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[%s] Failed to upload file to Dropbox.', __METHOD__), [
                'file' => $encodedFile->filename,
                'mediaType' => $encodedFile->mediaType,
                'size' => $encodedFile->size,
                'ex' => (string) $exception
            ]);

            throw $exception;
        }

        $downloadLink = str_replace('?dl=0', '?dl=1', $sharedLink['url']);

        return new HostedFileData(
            fileId: (string) $uploadedFile['id'],
            fileName: $uploadedFile['name'],
            webViewLink: $sharedLink['url'],
            webContentLink: $downloadLink
        );
    }

    private function generateFileName(string $fileName): string
    {
        $pathInfo = pathinfo($fileName);

        return "/{$pathInfo['filename']}-{$this->randomString()}.{$pathInfo['extension']}";
    }

    protected function randomString(): string
    {
        return bin2hex(random_bytes(6));
    }
}
