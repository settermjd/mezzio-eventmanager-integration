<?php

declare(strict_types=1);

namespace EventManagerIntegration\Iterator;

use ArrayIterator;
use EventManagerIntegrationTest\InMemoryContainer;
use EventManagerIntegrationTest\Listener\FakeLoggerListener;
use EventManagerIntegrationTest\Listener\FakeNotificationListener;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use function array_column;
use function iterator_to_array;
use function sort;

class ValidListenerFilterIteratorTest extends TestCase
{
    private InMemoryContainer $container;

    public function setUp(): void
    {
        $this->container = new InMemoryContainer();
    }

    /**
     * @param array<int,array{'listener': class-string, 'priority'?: int}> $eventListeners
     * @param array<class-string> $eventListenersToRegister
     */
    #[TestWith(
        [
            [
                [
                    'listener' => FakeLoggerListener::class,
                    'priority' => 10,
                ],
                [
                    'listener' => FakeNotificationListener::class,
                    'priority' => 20,
                ],
            ],
            [],
            0,
        ],
    )]
    #[TestWith(
        [
            [
                [
                    'listener' => FakeLoggerListener::class,
                    'priority' => 10,
                ],
                [
                    'listener' => FakeNotificationListener::class,
                    'priority' => 20,
                ],
            ],
            [
                FakeNotificationListener::class,
            ],
            1,
        ],
    )]
    #[TestWith(
        [
            [
                [
                    'listener' => FakeLoggerListener::class,
                    'priority' => 10,
                ],
                [
                    'listener' => FakeNotificationListener::class,
                    'priority' => 20,
                ],
            ],
            [
                FakeNotificationListener::class,
                FakeLoggerListener::class,
            ],
            2,
        ],
    )]
    public function testFiltersOutListenersNotRegisteredAsServices(
        array $eventListeners,
        array $eventListenersToRegister,
        int $validListenerCount
    ): void {
        foreach ($eventListenersToRegister as $eventListener) {
            $this->container->set($eventListener, new $eventListener());
        }
        $filter = new ValidListenerFilterIterator(new ArrayIterator($eventListeners), $this->container);
        $this->assertCount($validListenerCount, $filter);

        $registeredListeners = array_column(iterator_to_array($filter), 'listener');
        sort($registeredListeners);
        sort($eventListenersToRegister);
        $this->assertSame(
            $eventListenersToRegister,
            $registeredListeners
        );
    }
}
