<?php

namespace App\Domain\Common\Queue\Contracts;

interface Consumer
{
    public function consume(string $queue): void;
}
