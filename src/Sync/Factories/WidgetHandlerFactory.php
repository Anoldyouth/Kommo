<?php

declare(strict_types=1);

namespace Sync\Factories;

use Psr\Container\ContainerInterface;
use Sync\Handlers\WidgetHandler;

class WidgetHandlerFactory
{
    public function __invoke(ContainerInterface $container): WidgetHandler
    {
        return new widgetHandler();
    }
}
