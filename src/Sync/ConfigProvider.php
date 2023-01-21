<?php

declare(strict_types=1);

namespace Sync;

use Sync\CommandFactories\SetUpdateJobFactory;
use Sync\CommandFactories\StartUpdateTokensWorkerFactory;
use Sync\CommandFactories\TimeWorkerFactory;
use Sync\Commands\AddWorkerToDB;
use Sync\Commands\ClearWorkers;
use Sync\Commands\DeleteWorkerFromDB;
use Sync\Commands\NowTime;
use Sync\Commands\SetUpdateJob;
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
use Sync\Workers\TimeWorker;
use Sync\Workers\UpdateTokensWorker;

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
                UpdateTokensWorker::class => StartUpdateTokensWorkerFactory::class,
                SetUpdateJob::class => SetUpdateJobFactory::class,
                TimeWorker::class => TimeWorkerFactory::class,
            ],
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'Sync:now-time' => NowTime::class,
                'Sync:time-worker' => TimeWorker::class,
                'Sync:update-tokens' => SetUpdateJob::class,
                'Sync:add-worker' => AddWorkerToDB::class,
                'Sync:delete-worker' => DeleteWorkerFromDB::class,
                'Sync:start-update-tokens-worker' => UpdateTokensWorker::class,
                'Sync:clear-workers' => ClearWorkers::class,
            ],
        ];
    }
}
