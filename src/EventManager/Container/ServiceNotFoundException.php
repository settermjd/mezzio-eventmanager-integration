<?php

declare(strict_types=1);

namespace EventManagerIntegration\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{
}
