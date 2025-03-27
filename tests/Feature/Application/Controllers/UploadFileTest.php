<?php

declare(strict_types=1);

namespace Tests\Application\Sender;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Common\Services\Uuid\Contracts\UuidGeneratorService;
use App\Domain\Common\Queue\JobDispatcher;
use App\Domain\Common\Services\ZipArchive\ZipArchiveService;
use App\Domain\Sender\Contracts\FileHostingRepository;
use App\Domain\Sender\Contracts\FileRepository;
use App\Domain\Sender\Contracts\HostingRepository;
use App\Domain\Sender\DTOs\CreateFileHostingData;
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
    private ObjectProphecy $fileHostingRepositoryProphecy;
    private ObjectProphecy $hostingRepositoryProphecy;
    private ObjectProphecy $uuidGeneratorService;
    private ObjectProphecy $zipArchiveService;
    private ObjectProphecy $sendFileToHostingJob;
    private ObjectProphecy $jobDispatcher;

    protected function setup(): void
    {
        parent::setup();

        $this->app = $this->getAppInstance();
        $this->container = $this->app->getContainer();

        $this->faker = faker();

        $this->fileRepositoryProphecy = $this->prophesize(FileRepository::class);
        $this->fileHostingRepositoryProphecy = $this->prophesize(FileHostingRepository::class);
        $this->hostingRepositoryProphecy = $this->prophesize(HostingRepository::class);
        $this->uuidGeneratorService = $this->prophesize(UuidGeneratorService::class);
        $this->zipArchiveService = $this->prophesize(ZipArchiveService::class);
        $this->sendFileToHostingJob = $this->prophesize(SendFileToHostingJob::class);
        $this->jobDispatcher = $this->prophesize(JobDispatcher::class);
    }

    public function testShouldFailWhenFileIsNotSent(): void
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();
        $this->zipArchiveService->zipArchive()->shouldNotBeCalled();
        $this->sendFileToHostingJob->setArgs()->shouldNotBeCalled();
        $this->jobDispatcher->dispatch()->shouldNotBeCalled();

        $this->containerSetProphecyReveal();

        $request = $this->createRequest('POST', '/upload')
            ->withParsedBody(['hosting_slugs' => [$this->faker->slug(1)]]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals(StatusCode::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEqualsIgnoringCase('uploadedFiles cant be blank', $responseBody->error->description);
    }

    public function testShouldFailWhenTheHostingAreNotInformed(): void
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();
        $this->zipArchiveService->zipArchive()->shouldNotBeCalled();
        $this->sendFileToHostingJob->setArgs()->shouldNotBeCalled();
        $this->jobDispatcher->dispatch()->shouldNotBeCalled();

        $this->containerSetProphecyReveal();

        $uploadedFile = UploadedFileFactory::create();

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['file' => $uploadedFile]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals(StatusCode::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('hostingSlugs cant be blank', $responseBody->error->description);
    }

    public function testShouldFailWhenTheFileIsInError(): void
    {
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $this->app->add($errorMiddleware);

        $this->fileRepositoryProphecy->create()->shouldNotBeCalled();
        $this->fileHostingRepositoryProphecy->create()->shouldNotBeCalled();
        $this->hostingRepositoryProphecy->queryBySlugs()->shouldNotBeCalled();
        $this->uuidGeneratorService->generateUuid()->shouldNotBeCalled();
        $this->zipArchiveService->zipArchive()->shouldNotBeCalled();
        $this->sendFileToHostingJob->setArgs()->shouldNotBeCalled();
        $this->jobDispatcher->dispatch()->shouldNotBeCalled();

        $this->containerSetProphecyReveal();

        $hostingSlugs = [$this->faker->slug(1)];
        $uploadedFile = $uploadedFile = UploadedFileFactory::create(['error' => UPLOAD_ERR_NO_FILE]);

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['files' => [$uploadedFile]])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertEquals(StatusCode::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals("Error uploading file: {$uploadedFile->getClientFilename()}, No file was uploaded", $responseBody->error->description);
    }

    public function testShouldUploadTheFileSuccessfully(): void
    {
        $filename = $this->faker->filePath() . '.' . $this->faker->fileExtension();
        $mimeType = 'image/jpeg';
        $fileSize = 5 * 1024 * 1024; // 5MB
        $streamContent = 'any';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(2))
            ->method('__toString')
            ->willReturn($streamContent);

        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientFilename')
            ->willReturn($filename);
        $uploadedFile->expects($this->exactly(3))
            ->method('getClientMediaType')
            ->willReturn($mimeType);
        $uploadedFile->expects($this->exactly(4))
            ->method('getSize')
            ->willReturn($fileSize);
        $uploadedFile->expects($this->exactly(2))
            ->method('getStream')
            ->willReturn($stream);

        $createFile = CreateFileDataFactory::create([
            'name' => $filename,
            'size' => $fileSize,
            'mimeType' => $mimeType,
        ]);

        $fileId = $this->faker->randomDigitNotZero();
        $hostingSlugs = ['google-drive'];
        $googleDriveHosting = new HostingData($this->faker->randomDigitNotZero(), $hostingSlugs[0], 'Google Drive', null, null);
        $fileHostingId = $this->faker->randomDigitNotZero();

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

        $this->fileHostingRepositoryProphecy
            ->create(
                new CreateFileHostingData(
                    fileId: $fileId,
                    hostingId: $googleDriveHosting->id
                ),
            )
            ->willReturn($fileHostingId)
            ->shouldBeCalledOnce();

        $this->zipArchiveService
            ->zipArchive([$uploadedFile])
            ->shouldNotBeCalled();

        $this->sendFileToHostingJob
            ->setArgs(
                new SendFileToHostingData(
                    $googleDriveHosting->slug,
                    $fileHostingId,
                    new EncodedFileData(
                        $streamContent,
                        $filename,
                        $mimeType,
                        $fileSize,
                    ),
                )
            )->willReturn($this->sendFileToHostingJob->reveal())
            ->shouldBeCalledOnce();

        $this->jobDispatcher
            ->dispatch($this->sendFileToHostingJob->reveal())
            ->shouldBeCalledOnce();

        $this->containerSetProphecyReveal();

        $request = $this->createRequest('POST', '/upload')
            ->withUploadedFiles(['files' => [$uploadedFile]])
            ->withParsedBody(['hosting_slugs' => $hostingSlugs]);

        $response = $this->app->handle($request);

        $this->assertEquals(StatusCode::STATUS_CREATED, $response->getStatusCode());

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(['file_id' => $createFile->uuid], $responseBody);
    }

    private function containerSetProphecyReveal(): void
    {
        $this->container->set(FileRepository::class, $this->fileRepositoryProphecy->reveal());
        $this->container->set(FileHostingRepository::class, $this->fileHostingRepositoryProphecy->reveal());
        $this->container->set(HostingRepository::class, $this->hostingRepositoryProphecy->reveal());
        $this->container->set(UuidGeneratorService::class, $this->uuidGeneratorService->reveal());
        $this->container->set(SendFileToHostingJob::class, $this->sendFileToHostingJob->reveal());
        $this->container->set(ZipArchiveService::class, $this->zipArchiveService->reveal());
        $this->container->set(JobDispatcher::class, $this->jobDispatcher->reveal());
    }
}
