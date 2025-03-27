<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\FileHostingData;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class FileHostingDataFactory extends MockFactory
{
    public static function create(array $custom = []): FileHostingData
    {
        return new FileHostingData(
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
