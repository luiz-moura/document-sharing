<?php

declare(strict_types=1);

namespace Tests\Application\Sender;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Common\Uuid\Contracts\UuidGeneratorService;
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
    private $uuidGeneratorService;

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
        $this->uuidGeneratorService = $this->prophesize(UuidGeneratorService::class);
    }

    public function testShouldFailWhenFileIsNotSent()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingAction->__invoke()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingAction::class, $this->sendFileToHostingAction->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withParsedBody(['hosting_slugs' => [$this->faker->slug(1)]]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEqualsIgnoringCase($responseBody->error->description, 'uploadedFile cant be blank');
    }

    public function testShouldFailWhenTheHostingAreNotInformed()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingAction->__invoke()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingAction::class, $this->sendFileToHostingAction->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $uploadedFile = UploadedFileFactory::create();

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEquals($responseBody->error->description, 'hostingSlugs cant be blank');
    }

    public function testShouldFailWhenTheFileIsInError()
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingAction->__invoke()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingAction::class, $this->sendFileToHostingAction->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $hostingSlugs = [$this->faker->slug(1)];
        $uploadedFile = $uploadedFile = UploadedFileFactory::create(['error' => UPLOAD_ERR_NO_FILE]);

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEquals($responseBody->error->description, 'No file was uploaded');
    }

    public function testShouldUploadTheFileSuccessfully()
    {
        $fileId = $this->faker->randomDigitNotZero();
        $hostingSlugs = [$this->faker->randomDigitNotZero()];
        $googleDriveHosting = new HostingData($hostingSlugs[0], 'Google Drive', 'google-drive');
        $fileHostingId = $this->faker->randomDigitNotZero();

        $uploadedFile = UploadedFileFactory::create();
        $createdFile = CreateFileDataFactory::fromUploadedFile($uploadedFile);

        $this->hostingRepositoryProphecy
            ->queryBySlugs($hostingSlugs)
            ->willReturn([$googleDriveHosting])
            ->shouldBeCalledOnce();

        $this->uuidGeneratorService
            ->generateUuid()
            ->willReturn($createdFile->uuid)
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
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_CREATED);

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals($responseBody, ['file_id' => $createdFile->uuid]);
    }
}
