<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\TestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new TestHandler();
    }
}