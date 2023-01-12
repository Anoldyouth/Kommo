<?php

declare(strict_types=1);

namespace Sync\Factories;

use Psr\Container\ContainerInterface;
use Sync\Handlers\UnisenderHandler;

class UnisenderHandlerFactory
{
    public function __invoke(ContainerInterface $container): UnisenderHandler
    {
        return new UnisenderHandler();
    }
}
