<?php

declare(strict_types=1);

namespace Tests\Application\Sender;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Sender\Actions\SendFileToHostingAction;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use DI\Container;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Faker\Generator;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Tests\Utils\Mocks\Sender\CreateFileDataFactory;
use Tests\Utils\Mocks\Sender\UploadedFileFactory;
use Tests\TestCase;
use function Tests\Utils\Faker\faker;

class UploadFileTest extends TestCase
{
    private App $app;
    private Container $container;
    private Generator $faker;
    private $fileRepositoryProphecy;
    private $fileHostingRepositoryProphecy;
    private $hostingRepositoryProphecy;
    private $sendFileToHostingAction;

    protected function setup(): void
    {
        parent::setup();

        $this->app = $this->getAppInstance();
        $this->container = $this->app->getContainer();

        $this->faker = faker();

        $this->fileRepositoryProphecy = $this->prophesize(FileRepository::class);
        $this->fileHostingRepositoryProphecy = $this->prophesize(FileHostingRepository::class);
        $this->hostingRepositoryProphecy = $this->prophesize(HostingRepository::class);
        $this->sendFileToHostingAction = $this->prophesize(SendFileToHostingAction::class);
    }

    public function testShouldFailWhenFileIsNotSent()
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
        $this->sendFileToHostingAction->__invoke()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingAction::class, $this->sendFileToHostingAction->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withParsedBody(['hosting_ids' => [$this->faker->randomDigitNotZero()]]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEqualsIgnoringCase($responseBody->error->description, 'uploadedFile cant be blank');
    }

    public function testShouldFailWhenTheFileIsInError()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $hostingIds = [$this->faker->randomDigitNotZero()];
        $uploadedFile = $uploadedFile = UploadedFileFactory::create(['error' => UPLOAD_ERR_NO_FILE]);

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => $hostingIds]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEquals($responseBody->error->description, 'No file was uploaded');
    }

    public function testShouldFailWhenTheHostingAreNotInformed()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $uploadedFile = UploadedFileFactory::create();

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
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

        $uploadedFile = UploadedFileFactory::create();

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => ['INVALID_TYPE']]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEquals($responseBody->error->description, 'Only integer values are allowed in the hostingIds field.');
    }

    public function testShouldUploadTheFileSuccessfully()
    {
        $fileId = $this->faker->randomDigitNotZero();
        $hostingIds = [$this->faker->randomDigitNotZero()];
        $googleDriveHosting = new HostingData($hostingIds[0], 'Google Drive');
        $fileHostingId = $this->faker->randomDigitNotZero();

        $uploadedFile = UploadedFileFactory::create();
        $createdFile = CreateFileDataFactory::fromUploadedFile($uploadedFile);

        $this->hostingRepositoryProphecy
            ->queryByIds($hostingIds)
            ->willReturn([$googleDriveHosting])
            ->shouldBeCalledOnce();

        $this->fileRepositoryProphecy
            ->create($createdFile)
            ->willReturn($fileId)
            ->shouldBeCalledOnce();

        $this->fileHostingRepositoryProphecy
            ->create(
                new CreateFileHostingData(
                    fileId: $fileId,
                    hosting: $googleDriveHosting
                ),
            )
            ->willReturn($fileHostingId)
            ->shouldBeCalledOnce();

        $this->sendFileToHostingAction
            ->__invoke(
                new SendFileToHostingData(
                    $fileHostingId,
                    $googleDriveHosting,
                    $uploadedFile,
                )
            )
            ->shouldBeCalledOnce();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingAction::class, $this->sendFileToHostingAction->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_ids' => $hostingIds]);

        $response = $this->app->handle($request);

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_OK);
        $this->assertEmpty((string) $response->getBody());
    }
}
