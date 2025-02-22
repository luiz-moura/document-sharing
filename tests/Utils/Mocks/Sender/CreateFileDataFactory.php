<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\CreateFileData;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class CreateFileDataFactory extends MockFactory
{
    public static function create(array $custom = []): CreateFileData
    {
        return new CreateFileData(
            ...($custom + static::make())
        );
    }

    public static function make(): array
    {
        $faker = faker();

        return [
            'uuid' => $faker->uuid(),
            'name' => $faker->filePath(),
            'size' => $faker->randomDigitNotZero(),
            'mimeType' => $faker->mimeType(),
        ];
    }
}
