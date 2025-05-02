<?php

declare(strict_types=1);

namespace EventManagerIntegration\Container;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function gettype;
use function is_callable;
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

        /** @var array<class-string,array{'event': string, 'priority'?: int}> $listeners */
        $listeners = (array) $config['listeners'];
        if ($listeners !== []) {
            foreach ($listeners as $listener => $listenerConfig) {
                if (! $container->has($listener)) {
                    continue;
                }

                $listener = $container->get($listener);
                if (is_callable($listener)) {
                    $eventManager->attach(
                        eventName: $listenerConfig['event'],
                        listener: $listener,
                        priority: $listenerConfig['priority'] ?? self::DEFAULT_PRIORITY,
                    );
                }
            }
        }

        return $eventManager;
    }
}
