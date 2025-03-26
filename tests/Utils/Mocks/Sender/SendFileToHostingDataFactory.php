<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class SendFileToHostingDataFactory extends MockFactory
{
    public static function create(array $custom = []): SendFileToHostingData
    {
        return new SendFileToHostingData(
            ...($custom + static::make())
        );
    }

    public static function make(array $custom = []): array
    {
        $faker = faker();

        return $custom +  [
            'hostingSlug' => $faker->slug(),
            'hostedFileId' => $faker->randomDigitNotZero(),
            'encodedFile' => new EncodedFileData(
                base64: $faker->sha256(),
                filename: $faker->word(),
                mimeType: $faker->mimeType(),
                size: $faker->randomNumber(),
            ),
        ];
    }
}
