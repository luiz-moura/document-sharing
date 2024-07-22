<?php

namespace App\Domain\Sender\Contracts;

interface FileSenderFactory
{
    public function create(string $type): FileSenderService;
}
