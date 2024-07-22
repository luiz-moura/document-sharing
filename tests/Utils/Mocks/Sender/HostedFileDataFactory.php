<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\HostedFileData;
use Tests\Utils\Mocks\MockFactory;
use function Tests\Utils\Faker\faker;

class HostedFileDataFactory extends MockFactory
{
    public static function create(array $custom = []): HostedFileData
    {
        return new HostedFileData(
            ...($custom + static::getValues())
        );
    }

    public static function getValues(): array
    {
        $faker = faker();

        return [
            'fileId' => $faker->randomDigitNotZero(),
            'fileName' => $faker->filePath(),
            'webViewLink' => $faker->url(),
            'webContentLink' => $faker->url(),
        ];
    }
}
