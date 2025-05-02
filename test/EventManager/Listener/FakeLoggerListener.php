<?php

declare(strict_types=1);

namespace EventManagerIntegrationTest\Listener;

use Laminas\EventManager\Event;

class FakeLoggerListener
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
