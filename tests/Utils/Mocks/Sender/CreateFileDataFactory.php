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
            'name' => $faker->filePath(),
            'size' => $faker->randomDigitNotZero(),
            'mimeType' => $faker->mimeType(),
        ];
    }

    public static function fromUploadedFile(UploadedFileInterface $uploadedFile): CreateFileData
    {
        return new CreateFileData(
            $uploadedFile->getClientFilename(),
            $uploadedFile->getSize(),
            $uploadedFile->getClientMediaType()
        );
    }
}
