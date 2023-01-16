<?php

declare(strict_types=1);

namespace Sync;

use Sync\Commands\NowTime;
use Sync\Commands\TimeWorkerConstruct;
use Sync\Factories\ApiHandlerFactory;
use Sync\Factories\ContactHandlerFactory;
use Sync\Factories\ContactsHandlerFactory;
use Sync\Factories\SumHandlerFactory;
use Sync\Factories\SyncHandlerFactory;
use Sync\Factories\TestHandlerFactory;
use Sync\Factories\WebhookHandlerFactory;
use Sync\Factories\WidgetHandlerFactory;
use Sync\Handlers\ApiHandler;
use Sync\Handlers\ContactHandler;
use Sync\Handlers\ContactsHandler;
use Sync\Handlers\SumHandler;
use Sync\Handlers\SyncHandler;
use Sync\Handlers\TestHandler;
use Sync\Handlers\WebhookHandler;
use Sync\Handlers\WidgetHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'laminas-cli' => $this->getCliConfig(),
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
                ContactsHandler::class => ContactsHandlerFactory::class,
                ContactHandler::class => ContactHandlerFactory::class,
                SyncHandler::class => SyncHandlerFactory::class,
                WidgetHandler::class => WidgetHandlerFactory::class,
                WebhookHandler::class => WebhookHandlerFactory::class,
            ],
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'Sync:now-time' => NowTime::class,
                'Sync:time-worker' => TimeWorkerConstruct::class,
            ],
        ];
    }
}
