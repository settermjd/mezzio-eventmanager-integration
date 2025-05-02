<?php

declare(strict_types=1);

namespace EventManagerIntegration\Container;

use ArrayIterator;
use EventManagerIntegration\Iterator\ValidListenerFilterIterator;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function gettype;
use function is_object;
use function sprintf;

class ListenerConfigurationDelegator
{
    public const int DEFAULT_PRIORITY = 1;

    /**
     * Decorate an EventManager instance by attaching its listeners from configuration.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $serviceName,
        callable $callback
    ): EventManagerInterface {
        $eventManager = $callback();
        if (! $eventManager instanceof EventManagerInterface) {
            throw new ServiceNotFoundException(sprintf(
                'Delegator factory %s cannot operate on a %s; please map it only to the %s service',
                self::class,
                is_object($eventManager) ? $eventManager::class . ' instance' : gettype($eventManager),
                EventManager::class
            ));
        }

        if (! $container->has('config')) {
            return $eventManager;
        }

        $config = (array) $container->get('config');
        if (! array_key_exists('listeners', $config)) {
            return $eventManager;
        }

        /** @var array<string,array<int,array{'listener': class-string, 'priority'?: int}>> $listeners */
        $listeners = (array) $config['listeners'];
        if ($listeners !== []) {
            foreach ($listeners as $eventName => $eventListeners) {
                $iterator = new ValidListenerFilterIterator(new ArrayIterator($eventListeners), $container);
                foreach ($iterator as $eventListener) {
                    $listener = $container->get($eventListener['listener']);
                    $eventManager->attach(
                        eventName: $eventName,
                        listener: $listener,
                        priority: $eventListener['priority'] ?? self::DEFAULT_PRIORITY,
                    );
                }
            }
        }

        return $eventManager;
    }
}
