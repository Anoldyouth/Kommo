<?php

declare(strict_types=1);

namespace Sync\Factories;

use Psr\Container\ContainerInterface;
use Sync\Handlers\SyncHandler;

class SyncHandlerFactory
{
    public function __invoke(ContainerInterface $container): SyncHandler
    {
        return new SyncHandler();
    }
}
