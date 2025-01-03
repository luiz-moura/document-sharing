<?php

declare(strict_types=1);

namespace Tests\Utils\Mocks\Sender;

use App\Domain\Sender\DTOs\EncodedFileData;
use App\Domain\Sender\DTOs\HostingData;
use App\Domain\Sender\DTOs\SendFileToHostingData;
use Tests\Utils\Mocks\MockFactory;

use function Tests\Utils\Faker\faker;

class SendFileToHostingDataFactory extends MockFactory
{
    public static function create(array $custom = []): SendFileToHostingData
    {
        return new SendFileToHostingData(
            ...($custom + static::getValues())
        );
    }

    public static function getValues(): array
    {
        $faker = faker();

        return [
            'hostedFileId' => $faker->randomDigitNotZero(),
            'hosting' => new HostingData(
                id: $faker->randomDigitNotZero(),
                slug: $faker->slug(),
                name: $faker->monthName(),
            ),
            'encodedFile' => new EncodedFileData(
                filename: $faker->word(),
                mediaType: $faker->mimeType(),
                size: $faker->randomNumber(),
                base64: $faker->sha256(),
            ),
        ];
    }
}
