<?php

namespace Sync\CommandFactories;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Sync\config\BeanstalkConfig;
use Sync\Workers\UpdateTokensWorker;

class StartUpdateTokensWorkerFactory implements FactoryInterface
{
    /**
     * Фабрика для воркера, обновляющий токены
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
        return new UpdateTokensWorker(new BeanstalkConfig($container));
    }
}
