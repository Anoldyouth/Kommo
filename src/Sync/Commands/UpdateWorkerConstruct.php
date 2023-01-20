<?php

namespace Sync\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\config\BeanstalkConfig;
use Sync\Workers\TimeWorker;
use Sync\Workers\UpdateTokensWorker;
use Throwable;

class UpdateWorkerConstruct extends Command
{
    /**
     * Вызов TimeWorker
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            (new UpdateTokensWorker(new BeanstalkConfig()))->execute($input, $output);
        } catch (Throwable $ex) {
            $output->writeln('<error>Ошибка подключения к Beanstalk<error>');
        }
        return 0;
    }
}
