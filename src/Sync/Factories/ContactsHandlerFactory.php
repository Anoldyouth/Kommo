<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\ContactsHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContactsHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new ContactsHandler();
    }
}