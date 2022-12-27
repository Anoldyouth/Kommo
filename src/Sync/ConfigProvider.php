<?php

declare(strict_types=1);

namespace Sync;

use Sync\Factories\ApiHandlerFactory;
use Sync\Factories\SumHandlerFactory;
use Sync\Factories\TestHandlerFactory;
use Sync\Handlers\ApiHandler;
use Sync\Handlers\SumHandler;
use Sync\Handlers\TestHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories' => [
                TestHandler::class => TestHandlerFactory::class,
                SumHandler::class => SumHandlerFactory::class,
                ApiHandler::class => ApiHandlerFactory::class,
            ],
        ];
    }
}
