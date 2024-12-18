<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\CreateFileData;
use Psr\Http\Message\UploadedFileInterface;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class CreateFileDataFactory extends MockFactory
{
    public static function create(array $custom = []): CreateFileData
    {
        return new CreateFileData(
            ...($custom + static::getValues())
        );
    }

    public static function getValues(): array
    {
        $faker = faker();

        return [
            'uuid' => $faker->uuid(),
            'name' => $faker->filePath(),
            'size' => $faker->randomDigitNotZero(),
            'mimeType' => $faker->mimeType(),
        ];
    }

    public static function fromUploadedFile(UploadedFileInterface $uploadedFile): CreateFileData
    {
        $faker = faker();

        return new CreateFileData(
            $faker->uuid(),
            $uploadedFile->getClientFilename(),
            $uploadedFile->getSize(),
            $uploadedFile->getClientMediaType()
        );
    }
}
