<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Log\LoggerInterface;
use Spatie\Dropbox\Client as DropboxClient;
use Throwable;

class DropboxService implements FileSenderService
{
    private DropboxClient $client;

    public function __construct(
        private LoggerInterface $logger,
        private HostingRepository $hostingRepository
    ) {
        $appKey = config('dropbox.app_key');
        $appSecret = config('dropbox.app_secret');
        $accessCode = config('dropbox.access_code');

        $tokenProvider = new DropboxRefreshableTokenProvider(
            $appKey,
            $appSecret,
            $accessCode,
            $this->logger,
            $this->hostingRepository
        );
        $this->client = new DropboxClient($tokenProvider);
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
            $token = $this->client->getAccessToken();

            $this->client->setAccessToken($token);

            /**
             * @var array{path_display: string, id: string, name: string, size: string, path_lower: string} $uploadedFile
             */
            $uploadedFile = $this->client->upload($fileName, $encodedFile->base64, 'add');

            $sharedLink = $this->createSharedLink($uploadedFile['path_display']);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[%s] Failed to upload file to Dropbox.', __METHOD__), [
                'file' => $encodedFile->filename,
                'mediaType' => $encodedFile->mediaType,
                'size' => $encodedFile->size,
                'ex' => (string) $exception
            ]);

            throw $exception;
        }

        $downloadLink = str_replace('?dl=0', '?dl=1', $sharedLink);

        return new HostedFileData(
            fileId: (string) $uploadedFile['id'],
            fileName: $uploadedFile['name'],
            webViewLink: $sharedLink,
            webContentLink: $downloadLink
        );
    }

    protected function randomString(): string
    {
        return bin2hex(random_bytes(6));
    }

    private function generateFileName(string $fileName): string
    {
        $pathInfo = pathinfo($fileName);

        return "/{$pathInfo['filename']}-{$this->randomString()}.{$pathInfo['extension']}";
    }

    private function createSharedLink(string $path): string
    {
        /**
         * @var array{url: string, name: string, size: string, path_lower: string} $sharedLink
         */
        $sharedLink = $this->client->createSharedLinkWithSettings($path);

        return $sharedLink['url'];
    }
}
