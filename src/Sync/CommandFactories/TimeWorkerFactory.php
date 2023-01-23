<?php

namespace Sync\CommandFactories;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Sync\config\BeanstalkConfig;
use Sync\Workers\TimeWorker;

class TimeWorkerFactory implements FactoryInterface
{
    /**
     * Фабрика для создания задачи вывода времени
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Command
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): Command {
        return new TimeWorker(new BeanstalkConfig($container));
    }
}
