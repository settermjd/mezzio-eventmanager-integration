<?php

declare(strict_types=1);

namespace EventManagerIntegrationTest\Container;

use EventManagerIntegration\Container\ListenerConfigurationDelegator;
use EventManagerIntegration\Container\ServiceNotFoundException;
use EventManagerIntegrationTest\InMemoryContainer;
use EventManagerIntegrationTest\Listeners\FakeLoggerListener;
use EventManagerIntegrationTest\Listeners\FakeNotificationListener;
use Laminas\EventManager\EventManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function sprintf;

class ListenerConfigurationDelegatorTest extends TestCase
{
    private InMemoryContainer $container;

    public function setUp(): void
    {
        $this->container = new InMemoryContainer();
    }

    public function createEventManager(): EventManager
    {
        return new EventManager();
    }

    public function testInvocationAsDelegatorFactoryRaisesExceptionIfCallbackIsNotAnApplication(): void
    {
        $callback = fn(): self => $this;
        $factory  = new ListenerConfigurationDelegator();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Delegator factory %s cannot operate on a %s instance; please map it only to the %s service',
                ListenerConfigurationDelegator::class,
                self::class,
                EventManager::class,
            )
        );
        $factory($this->container, EventManager::class, $callback);
    }

    public function testReturnsOriginalEventManagerIfConfigNotAvailable(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());
        $object       = new ReflectionClass($eventManager);
        $property     = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertEmpty($events);
    }

    public function testReturnsOriginalEventManagerIfNoListenersAreAvailable(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'lesteners' => [
                    FakeLoggerListener::class       => [
                        'event'    => 'test-event',
                        'priority' => 10,
                    ],
                    FakeNotificationListener::class => [
                        'event'    => 'test-event',
                        'priority' => 20,
                    ],
                ],
            ]);

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());
        $object       = new ReflectionClass($eventManager);
        $property     = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertEmpty($events);
    }

    public function testOnlyAddsListenersThatAreRegisteredAsServices(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'listeners' => [
                    FakeLoggerListener::class       => [
                        'event'    => 'test-event',
                        'priority' => 10,
                    ],
                    FakeNotificationListener::class => [
                        'event'    => 'test-event',
                        'priority' => 20,
                    ],
                ],
            ]);
        $this->container->set(FakeLoggerListener::class, new FakeLoggerListener());

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());

        $object   = new ReflectionClass($eventManager);
        $property = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertArrayHasKey('test-event', $events);
        $this->assertCount(1, $events['test-event']);
        $this->assertEquals(
            [
                10 => [
                    [
                        new FakeLoggerListener(),
                    ],
                ],
            ],
            $events['test-event']
        );
    }

    public function testAddsListenersWithDefaultPriorityIfNoPriorityIsSet(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'listeners' => [
                    FakeLoggerListener::class       => [
                        'event' => 'test-event',
                    ],
                    FakeNotificationListener::class => [
                        'event'    => 'test-event',
                        'priority' => 20,
                    ],
                ],
            ]);
        $this->container->set(FakeLoggerListener::class, new FakeLoggerListener());
        $this->container->set(FakeNotificationListener::class, new FakeNotificationListener());

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());

        $object   = new ReflectionClass($eventManager);
        $property = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertArrayHasKey('test-event', $events);
        $this->assertCount(2, $events['test-event']);
        $this->assertEquals(
            [
                20                                               => [
                    [
                        new FakeNotificationListener(),
                    ],
                ],
                ListenerConfigurationDelegator::DEFAULT_PRIORITY => [
                    [
                        new FakeLoggerListener(),
                    ],
                ],
            ],
            $events['test-event']
        );
    }

    public function testCanAddListeners(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'listeners' => [
                    FakeLoggerListener::class       => [
                        'event'    => 'test-event',
                        'priority' => 10,
                    ],
                    FakeNotificationListener::class => [
                        'event'    => 'test-event',
                        'priority' => 20,
                    ],
                ],
            ]);
        $this->container->set(FakeLoggerListener::class, new FakeLoggerListener());
        $this->container->set(FakeNotificationListener::class, new FakeNotificationListener());

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());

        $object   = new ReflectionClass($eventManager);
        $property = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertArrayHasKey('test-event', $events);
        $this->assertCount(2, $events['test-event']);
        $this->assertEquals(
            [
                10 => [
                    [
                        new FakeLoggerListener(),
                    ],
                ],
                20 => [
                    [
                        new FakeNotificationListener(),
                    ],
                ],
            ],
            $events['test-event']
        );
    }
}
