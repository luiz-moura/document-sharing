<?php

namespace App\Domain\Common\Event;

use App\Domain\Sender\Events\FileSentToHostEvent;
use App\Domain\Sender\Listeners\NotifyFileSentToHostListener;
use Psr\EventDispatcher\ListenerProviderInterface;

final class ListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [
        FileSentToHostEvent::class => [
            new NotifyFileSentToHostListener(),
        ],
    ];

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[get_class($event)] ?? [];
    }
}
