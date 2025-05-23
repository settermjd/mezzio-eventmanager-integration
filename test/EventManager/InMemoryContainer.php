<?php

declare(strict_types=1);

namespace EventManagerIntegrationTest;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

use function array_key_exists;

final class InMemoryContainer implements ContainerInterface
{
    /** @var array<string,mixed> */
    private array $services = [];

    /** @inheritDoc */
    public function get($id): mixed
    {
        if (! array_key_exists($id, $this->services)) {
            throw new class ($id . ' was not found') extends RuntimeException implements NotFoundExceptionInterface {
            };
        }

        /** @psalm-return mixed */

        return $this->services[$id];
    }

    /** @param string $id */
    public function has($id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function set(string $id, mixed $item): void
    {
        $this->services[$id] = $item;
    }

    public function reset(): void
    {
        $this->services = [];
    }
}
