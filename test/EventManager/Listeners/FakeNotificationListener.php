<?php

declare(strict_types=1);

namespace EventManagerIntegrationTest\Listeners;

use Laminas\EventManager\Event;

class FakeNotificationListener
{
    /**
     * @template TTarget of object|null
     * @template TParams of array
     * @param Event<TTarget, TParams> $event
     */
    public function __invoke(Event $event): void
    {
    }
}
