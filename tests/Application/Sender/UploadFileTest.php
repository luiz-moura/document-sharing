<?php

declare(strict_types=1);

namespace Tests\Application\Sender;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use DI\Container;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\UploadedFile;
use Tests\TestCase;

class UploadFileTest extends TestCase
{
    private App $app;
    private Container $container;
    private $fileRepositoryProphecy;
    private $fileHostingRepositoryProphecy;
    private $hostingRepositoryProphecy;
    private $sendFileToHostingJob;

    protected function setup(): void
    {
        parent::setup();

        $this->app = $this->getAppInstance();

        /** @var Container $container */
        $this->container = $this->app->getContainer();

        $this->fileRepositoryProphecy = $this->prophesize(FileRepository::class);
        $this->fileHostingRepositoryProphecy = $this->prophesize(FileHostingRepository::class);
        $this->hostingRepositoryProphecy = $this->prophesize(HostingRepository::class);
        $this->sendFileToHostingJob = $this->prophesize(SendFileToHostingJob::class);
    }

    public function testShouldUploadTheFileSuccessfully()
    {
        $fileId = 900;
        $hostingIds = [100];
        $googleDriveHosting = new HostingData($hostingIds[0], 'Google Drive');

        $uploadedFile = new UploadedFile(
            __DIR__ . '/example.png',
            'example.png',
            'image/png',
            300
        );

        $this->hostingRepositoryProphecy
            ->queryByIds($hostingIds)
            ->willReturn([$googleDriveHosting])
            ->shouldBeCalledOnce();

        $this->fileRepositoryProphecy
            ->create(
                new CreateFileData(
                    name: 'example.png',
                    size: 300,
                    mimeType: 'image/png'
                )
            )
            ->willReturn($fileId)
            ->shouldBeCalledOnce();

        $this->fileHostingRepositoryProphecy
            ->create(
                new CreateFileHostingData(
                    fileId: $fileId,
                    hosting: $googleDriveHosting
                ),
            )
            ->willReturn(100)
            ->shouldBeCalledOnce();

        $this->sendFileToHostingJob
            ->dispatch(
                new SendFileToHostingData(
                    $uploadedFile,
                    $googleDriveHosting
                )
            )
            ->shouldBeCalledOnce();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => $hostingIds]);

        $response = $this->app->handle($request);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals((string) $response->getBody(), null);
    }

    public function testShouldFailWhenFileIsNotUploaded()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryByIds()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingJob->dispatch()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withParsedBody(['hosting_ids' => [100]]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), 500);
        $this->assertEqualsIgnoringCase($responseBody->error->description, 'upladedFile cant be blank');
    }

    public function testShouldFailWhenTheFileIsInError()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $hostingIds = [100];
        $uploadedFile = new UploadedFile(
            __DIR__ . '/example.png',
            'example.png',
            'image/png',
            300,
            UPLOAD_ERR_NO_FILE
        );

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => $hostingIds]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), 500);
        $this->assertEquals($responseBody->error->description, 'No file was uploaded');
    }

    public function testShouldFailWhenTheHostingsAreNotInformed()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);
        $uploadedFile = new UploadedFile(
            __DIR__ . '/example.png',
            'example.png',
            'image/png',
            300,
            UPLOAD_ERR_NO_FILE
        );

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), 500);
        $this->assertEquals($responseBody->error->description, 'hostingIds cant be blank');
    }

    public function testShouldFailWhenOneOrMoreHostingIsInvalid()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);
        $uploadedFile = new UploadedFile(
            __DIR__ . '/example.png',
            'example.png',
            'image/png',
            300,
            UPLOAD_ERR_NO_FILE
        );

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => ['error']]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), 500);
        $this->assertEquals($responseBody->error->description, 'Only integer values are allowed in the hostingIds field.');
    }
}
