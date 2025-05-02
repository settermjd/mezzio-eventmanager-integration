<?php

declare(strict_types=1);

namespace EventManagerIntegrationTest\Container;

use EventManagerIntegration\Container\ListenerConfigurationDelegator;
use EventManagerIntegration\Container\ServiceNotFoundException;
use EventManagerIntegrationTest\InMemoryContainer;
use EventManagerIntegrationTest\Listener\FakeLoggerListener;
use EventManagerIntegrationTest\Listener\FakeNotificationListener;
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
                'lesteners' => [],
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
            ->set(
                'config',
                [
                    'listeners' => [
                        'add-item'    => [
                            [
                                'listener' => FakeLoggerListener::class,
                                'priority' => 10,
                            ],
                            [
                                'listener' => FakeNotificationListener::class,
                                'priority' => 10,
                            ],
                        ],
                        'update-item' => [
                            [
                                'listener' => FakeLoggerListener::class,
                            ],
                        ],
                        'delete-item' => [
                            [
                                'listener' => FakeLoggerListener::class,
                                'priority' => 10,
                            ],
                        ],
                    ],
                ]
            );
        $this->container->set(FakeLoggerListener::class, new FakeLoggerListener());

        $eventManager = $delegator($this->container, EventManager::class, fn() => $this->createEventManager());

        $object   = new ReflectionClass($eventManager);
        $property = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertArrayHasKey('add-item', $events);
        $this->assertCount(1, $events['add-item']);
        $this->assertEquals(
            [
                10 => [
                    [
                        new FakeLoggerListener(),
                    ],
                ],
            ],
            $events['add-item']
        );
    }

    public function testAddsListenersWithDefaultPriorityIfNoPriorityIsSet(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'listeners' => [
                    'add-item' => [
                        [
                            'listener' => FakeLoggerListener::class,
                        ],
                        [
                            'listener' => FakeNotificationListener::class,
                            'priority' => 20,
                        ],
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
        $this->assertArrayHasKey('add-item', $events);
        $this->assertCount(2, $events['add-item']);
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
            $events['add-item']
        );
    }

    public function testCanAddListeners(): void
    {
        $delegator = new ListenerConfigurationDelegator();

        $this->container
            ->set('config', [
                'listeners' => [
                    'add-item'    => [
                        [
                            'listener' => FakeLoggerListener::class,
                            'priority' => 10,
                        ],
                        [
                            'listener' => FakeNotificationListener::class,
                            'priority' => 10,
                        ],
                    ],
                    'update-item' => [
                        [
                            'listener' => FakeLoggerListener::class,
                        ],
                    ],
                    'delete-item' => [
                        [
                            'listener' => FakeLoggerListener::class,
                            'priority' => 10,
                        ],
                    ],
                ],
            ]);
        $this->container->set(FakeLoggerListener::class, new FakeLoggerListener());
        $this->container->set(FakeNotificationListener::class, new FakeNotificationListener());

        $eventManager = $delegator(
            $this->container,
            EventManager::class,
            fn() => $this->createEventManager()
        );

        $object   = new ReflectionClass($eventManager);
        $property = $object->getProperty('events');

        /** @var array<string,array<int,array<int,callable>>> $events */
        $events = $property->getValue($eventManager);
        $this->assertArrayHasKey('add-item', $events);
        $this->assertEquals(
            [
                10 => [
                    [
                        new FakeLoggerListener(),
                        new FakeNotificationListener(),
                    ],
                ],
            ],
            $events['add-item']
        );
    }
}
