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
    private readonly DropboxClient $client;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HostingRepository $hostingRepository
    ) {
        // TODO: use settings to set
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
            'filename' => $encodedFile->filename,
            'mediaType' => $encodedFile->mediaType,
            'size' => $encodedFile->size,
        ]);

        $filename = $this->generateFilename($encodedFile->filename);

        try {
            $token = $this->client->getAccessToken();

            $this->client->setAccessToken($token);

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
                'mediaType' => $encodedFile->mediaType,
                'size' => $encodedFile->size,
                'exception' => strval($exception),
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

    protected function randomString(): string
    {
        return bin2hex(random_bytes(6));
    }

    private function generateFilename(string $filename): string
    {
        $pathInfo = pathinfo($filename);

        return sprintf('/%s-%s.%s', $pathInfo['filename'], $this->randomString(), $pathInfo['extension']);
    }
}
