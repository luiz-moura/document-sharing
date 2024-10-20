<?php

declare(strict_types=1);

namespace Tests\Application\Sender;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Common\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Sender\Contracts\HostedFileRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use App\Domain\Sender\Jobs\SendFileToHostingJob;
use DI\Container;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Faker\Generator;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
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
    private $hostedFileRepositoryProphecy;
    private $hostingRepositoryProphecy;
    private $sendFileToHostingJob;
    private $uuidGeneratorService;

    protected function setup(): void
    {
        parent::setup();

        $this->app = $this->getAppInstance();
        $this->container = $this->app->getContainer();

        $this->faker = faker();

        $this->fileRepositoryProphecy = $this->prophesize(FileRepository::class);
        $this->hostedFileRepositoryProphecy = $this->prophesize(HostedFileRepository::class);
        $this->hostingRepositoryProphecy = $this->prophesize(HostingRepository::class);
        $this->sendFileToHostingJob = $this->prophesize(SendFileToHostingJob::class);
        $this->uuidGeneratorService = $this->prophesize(UuidGeneratorService::class);
    }

    public function testShouldFailWhenFileIsNotSent(): void
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->hostedFileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingJob->dispatch()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(HostedFileRepository::class, $this->hostedFileRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withParsedBody(['hosting_slugs' => [$this->faker->slug(1)]]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertEqualsIgnoringCase($responseBody->error->description, 'uploadedFile cant be blank');
    }

    public function testShouldFailWhenTheHostingAreNotInformed(): void
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->hostedFileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingJob->dispatch()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(HostedFileRepository::class, $this->hostedFileRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());
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
        $this->hostedFileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->sendFileToHostingJob->dispatch()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(HostedFileRepository::class, $this->hostedFileRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());
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

    public function testShouldUploadTheFileSuccessfully(): void
    {
        $fileId = $this->faker->randomDigitNotZero();
        $hostingSlugs = [$this->faker->randomDigitNotZero()];
        $googleDriveHosting = new HostingData($hostingSlugs[0], 'Google Drive', 'google-drive');
        $hostedFileId = $this->faker->randomDigitNotZero();

        $uploadedFile = UploadedFileFactory::create();
        $createdFile = CreateFileDataFactory::fromUploadedFile($uploadedFile);

        $streamContent = 'any';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(1))
            ->method('__toString')
            ->willReturn($streamContent);

        $uploadFile = $this->createMock(UploadedFileInterface::class);

        $uploadFile->expects($this->exactly(2))
            ->method('getClientFilename')
            ->willReturn($uploadedFile->getClientFilename());

        $uploadFile->expects($this->exactly(2))
            ->method('getClientMediaType')
            ->willReturn($uploadedFile->getClientMediaType());

        $uploadFile->expects($this->exactly(2))
            ->method('getSize')
            ->willReturn($uploadedFile->getSize());

        $uploadFile->expects($this->exactly(1))
            ->method('getStream')
            ->willReturn($stream);

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

        $this->hostedFileRepositoryProphecy
            ->create(
                new CreateHostedFileData(
                    fileId: $fileId,
                    hosting: $googleDriveHosting
                ),
            )
            ->willReturn($hostedFileId)
            ->shouldBeCalledOnce();

        $this->sendFileToHostingJob
            ->setArgs(
                new SendFileToHostingData(
                    $googleDriveHosting,
                    $hostedFileId,
                    new EncodedFileData(
                        $uploadedFile->getClientFilename(),
                        $uploadedFile->getClientMediaType(),
                        $uploadedFile->getSize(),
                        $streamContent,
                    ),
                )
            )
            ->shouldBeCalledOnce()
            ->willReturn($this->sendFileToHostingJob->reveal());

        $this->sendFileToHostingJob
            ->dispatch()
            ->shouldBeCalledOnce();

        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(HostedFileRepository::class, $this->hostedFileRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadFile])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_CREATED);

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals($responseBody, ['file_id' => $createdFile->uuid]);
    }
}
