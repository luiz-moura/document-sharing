<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Domain\Sender\Actions\UploadFileAction;
use App\Domain\Sender\DTOs\UploadRequestData;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UploadController
{
    public function __construct(private UploadFileAction $uploadFileAction) {}

    public function upload(Request $request, Response $response): Response
    {
        $uploadRequest = new UploadRequestData(
            hostingIds: $request->getParsedBody()['hosting_ids'] ?? null,
            uploadedFile: $request->getUploadedFiles()['file'] ?? null
        );

        ($this->uploadFileAction)($uploadRequest);

        return $response->withStatus(200);
    }
}
