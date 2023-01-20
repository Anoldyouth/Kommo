<?php

namespace Sync\CommandFactories;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Sync\config\BeanstalkConfig;
use Sync\DatabaseFunctions;
use Sync\Models\Worker;
use Sync\Workers\UpdateTokensWorker;

class StartUpdateTokensWorkerFactory implements FactoryInterface
{
    /**
     * Фабрика для воркера, обновляющий токены
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UpdateTokensWorker
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): UpdateTokensWorker {

        return new UpdateTokensWorker(new BeanstalkConfig($container));
    }
}