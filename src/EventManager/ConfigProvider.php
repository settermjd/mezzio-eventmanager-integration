<?php

declare(strict_types=1);

namespace EventManagerIntegration;

use EventManagerIntegration\Container\ListenerConfigurationDelegator;
use Laminas\EventManager\EventManager;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array{dependencies: array{delegators: array<class-string,array<int,class-string>>}}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array{delegators: array<class-string,array<int,class-string>>}
     */
    private function getDependencies(): array
    {
        return [
            'delegators' => [
                EventManager::class => [
                    ListenerConfigurationDelegator::class,
                ],
            ],
        ];
    }
}
