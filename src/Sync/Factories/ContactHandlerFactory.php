<?php

declare(strict_types=1);

namespace Sync\Factories;

use Psr\Container\ContainerInterface;
use Sync\Handlers\ContactHandler;

class ContactHandlerFactory
{
    public function __invoke(ContainerInterface $container): ContactHandler
    {
        return new ContactHandler();
    }
}
