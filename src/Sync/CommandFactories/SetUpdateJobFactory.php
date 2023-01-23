<?php

namespace Sync\CommandFactories;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Sync\Commands\SetUpdateJob;
use Sync\config\BeanstalkConfig;

class SetUpdateJobFactory implements FactoryInterface
{
    /**
     * Фабрика для создания задачи обновления
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
        return new SetUpdateJob(new BeanstalkConfig($container));
    }
}
