<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\FileSenderService;
use App\Domain\Sender\DTOs\HostedFileData;
use Psr\Http\Message\UploadedFileInterface;
use Spatie\Dropbox\Client as DropboxClient;

class DropboxService implements FileSenderService
{
    private DropboxClient $client;

    public function __construct()
    {
        $dropboxToken = config('dropbox.token');
        $this->client = new DropboxClient($dropboxToken);
    }

    public function send(UploadedFileInterface $fileToUpload): HostedFileData
    {
        $fileName = $this->generateFileName($fileToUpload->getClientFilename());

        $uploadedFile = $this->client->upload(
            $fileName,
            file_get_contents($fileToUpload->getStream()->getMetadata('uri')),
            'add'
        );

        $sharedLink = $this->client->createSharedLinkWithSettings($uploadedFile['path_display']);

        $previewLink = $sharedLink['url'];
        $downloadLink = str_replace('?dl=0', '?dl=1', $previewLink);

        return new HostedFileData(
            fileId: (string) $uploadedFile['id'],
            fileName: $uploadedFile['name'],
            webViewLink: $previewLink,
            webContentLink: $downloadLink
        );
    }

    private function generateFileName(string $fileName): string
    {
        $pathInfo = pathinfo($fileName);

        return "/{$pathInfo['filename']}-{$this->randomString()}.{$pathInfo['extension']}";
    }

    private function randomString(): string
    {
        return bin2hex(random_bytes(6));
    }
}
