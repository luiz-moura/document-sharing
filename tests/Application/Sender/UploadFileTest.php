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
use Prophecy\Prophecy\ObjectProphecy;
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
    private ObjectProphecy $fileRepositoryProphecy;
    private ObjectProphecy $hostedFileRepositoryProphecy;
    private ObjectProphecy $hostingRepositoryProphecy;
    private ObjectProphecy $sendFileToHostingJob;
    private ObjectProphecy $uuidGeneratorService;

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

    public function testShouldFailWhenTheFileIsInError(): void
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
        $fileSize = 5 * 1024 * 1024; // 5MB
        $fileType = 'image/jpeg';
        $fileName = $this->faker->filePath() . '.' . $this->faker->fileExtension();
        $streamContent = 'any';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(2))
            ->method('__toString')
            ->willReturn($streamContent);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->exactly(2))
            ->method('getClientFilename')
            ->willReturn($fileName);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientMediaType')
            ->willReturn($fileType);
        $uploadedFile->expects($this->exactly(3))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->exactly(2))
            ->method('getStream')
            ->willReturn($stream);

        $createFile = CreateFileDataFactory::create([
            'name' => $fileName,
            'size' => $fileSize,
            'mimeType' => $fileType,
        ]);

        $fileId = $this->faker->randomDigitNotZero();
        $hostingSlugs = ['google-drive'];
        $googleDriveHosting = new HostingData($this->faker->randomDigitNotZero(), $hostingSlugs[0], 'Google Drive');
        $hostedFileId = $this->faker->randomDigitNotZero();

        $this->hostingRepositoryProphecy
            ->queryBySlugs($hostingSlugs)
            ->willReturn([$googleDriveHosting])
            ->shouldBeCalledOnce();

        $this->uuidGeneratorService
            ->generateUuid()
            ->willReturn($createFile->uuid)
            ->shouldBeCalledOnce();

        $this->fileRepositoryProphecy
            ->create($createFile)
            ->willReturn($fileId)
            ->shouldBeCalledOnce();

        $this->hostedFileRepositoryProphecy
            ->create(
                new CreateHostedFileData(
                    fileId: $fileId,
                    hostingId: $googleDriveHosting->id
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
                        $fileName,
                        $fileType,
                        $fileSize,
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
            ->withUploadedFiles(['file' => $uploadedFile])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $this->assertEquals($response->getStatusCode(), StatusCode::STATUS_CREATED);

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals($responseBody, ['file_id' => $createFile->uuid]);
    }
}
