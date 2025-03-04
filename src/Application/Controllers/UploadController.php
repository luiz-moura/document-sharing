<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Domain\Sender\Actions\UploadFileAction;
use App\Domain\Sender\DTOs\UploadRequestData;
use App\Domain\Sender\Enums\FileStatusOnHostEnum;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UploadController
{
    public function __construct(
        private readonly UploadFileAction $uploadFileAction,
    ) {
    }

    public function upload(Request $request, Response $response): Response
    {
        $this->validateRequest($request);

        $uploadRequest = new UploadRequestData(
            hostingSlugs: $request->getParsedBody()['hosting_slugs'],
            uploadedFiles: $request->getUploadedFiles()['files'],
            shouldZip: filter_var($request->getParsedBody()['should_zip'] ?? false, FILTER_VALIDATE_BOOLEAN),
        );

        $fileUuid = ($this->uploadFileAction)($uploadRequest);

        $response->getBody()->write(
            json_encode([
                'file_id' => $fileUuid,
                'status' => FileStatusOnHostEnum::RECEIVED->value
            ])
        );

        return $response
            ->withStatus(StatusCode::STATUS_CREATED)
            ->withHeader('Content-Type', 'application/json');
    }

    private function validateRequest(Request $request): void
    {
        $parsedBody = $request->getParsedBody();

        if (! isset($parsedBody['hosting_slugs']) || ! is_array($parsedBody['hosting_slugs'])) {
            throw new InvalidArgumentException('hosting_slugs must be an array');
        }

        if (! isset($request->getUploadedFiles()['files'])) {
            throw new InvalidArgumentException('files must be uploaded');
        }
    }
}
