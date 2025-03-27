<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\FileOnHostingData;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class FileOnHostingDataFactory extends MockFactory
{
    public static function create(array $custom = []): FileOnHostingData
    {
        return new FileOnHostingData(
            ...($custom + static::make())
        );
    }

    public static function make(array $custom = []): array
    {
        $faker = faker();

        return $custom + [
            'fileId' => $faker->shuffleString(),
            'filename' => $faker->filePath(),
            'webViewLink' => $faker->url(),
            'webContentLink' => $faker->url(),
        ];
    }
}
