<?php

declare(strict_types=1);

namespace EventManagerIntegration\Iterator;

use FilterIterator;
use Iterator;
use Psr\Container\ContainerInterface;

use function is_callable;

/**
 * @template TKey of int
 * @template TValue of array{'listener': class-string, 'priority'?: int}
 * @template TIterator of Iterator
 * @template-extends FilterIterator<TKey, TValue, TIterator>
 */
class ValidListenerFilterIterator extends FilterIterator
{
    public function __construct(Iterator $iterator, private readonly ContainerInterface $container)
    {
        parent::__construct($iterator);
    }

    /**
     * @inheritDoc
     */
    public function accept(): bool
    {
        $listenerConfig = (array) $this->current();

        if (! $this->container->has($listenerConfig['listener'])) {
            return false;
        }

        $listener = $this->container->get($listenerConfig['listener']);
        if (! is_callable($listener)) {
            return false;
        }

        return true;
    }
}
