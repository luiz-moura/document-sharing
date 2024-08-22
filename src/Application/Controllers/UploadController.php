<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Domain\Sender\Actions\UploadFileAction;
use App\Domain\Sender\DTOs\UploadRequestData;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UploadController
{
    public function __construct(
        private UploadFileAction $uploadFileAction,
    ) {}

    public function upload(Request $request, Response $response): Response
    {
        $uploadRequest = new UploadRequestData(
            hostingSlugs: $request->getParsedBody()['hosting_slugs'] ?? null,
            uploadedFile: $request->getUploadedFiles()['file'] ?? null
        );

        $fileUuid = ($this->uploadFileAction)($uploadRequest);

        $response->getBody()->write(
            json_encode(['file_id' => $fileUuid])
        );

        return $response
            ->withStatus(StatusCode::STATUS_CREATED)
            ->withHeader('Content-Type', 'application/json');
    }
}
