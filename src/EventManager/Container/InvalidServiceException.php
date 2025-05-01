<?php

declare(strict_types=1);

namespace EventManagerIntegration\Container;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class InvalidServiceException extends RuntimeException implements ContainerExceptionInterface
{
}
