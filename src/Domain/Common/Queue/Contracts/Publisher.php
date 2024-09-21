<?php

namespace App\Domain\Common\Queue\Contracts;

interface Publisher
{
    public function publish(mixed $message, string $queue): void;
}
