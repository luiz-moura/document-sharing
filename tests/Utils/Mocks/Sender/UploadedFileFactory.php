<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class UploadedFileFactory extends MockFactory
{
    /**
     * @param array{size: int, error: string, type: string} $custom
     */
    public static function create(array $custom = []): UploadedFileInterface
    {
        return new UploadedFile(
            ...($custom + static::make())
        );
    }

    public static function make(): array
    {
        $faker = faker();

        return [
            'fileNameOrStream' => $faker->filePath() . '.' . $faker->fileExtension(),
            'name' => $faker->filePath(),
            'type' => $faker->mimeType(),
            'size' => $faker->randomDigitNotZero(),
            'error' => UPLOAD_ERR_OK
        ];
    }
}
