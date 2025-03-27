<?php

namespace App\Domain\Sender\Actions;

use DateTime;

class GenerateFilenameAction
{
    public function __invoke(string $identification, string $extension): string
    {
        $date = (new DateTime('now'))->format('YmdHis');

        return sprintf('%s_%s.%s', $date, $identification, $extension);
    }
}
