<?php

namespace Sync\CommandFactories;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Sync\Commands\SetUpdateJob;
use Sync\config\BeanstalkConfig;
use Sync\DatabaseFunctions;
use Sync\Models\Worker;
use Sync\Workers\TimeWorker;
use Sync\Workers\UpdateTokensWorker;

class TimeWorkerFactory implements FactoryInterface
{
    /**
     * Фабрика для создания задачи вывода времени
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TimeWorker
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): TimeWorker {
        return new TimeWorker(new BeanstalkConfig($container));
    }
}
