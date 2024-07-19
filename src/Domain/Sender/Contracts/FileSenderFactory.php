<?php

namespace App\Domain\Sender\Contracts;

interface FileSenderFactory
{
    public static function create(string $type): FileSenderService;
}
