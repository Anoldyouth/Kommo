<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\ApiHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new ApiHandler();
    }
}