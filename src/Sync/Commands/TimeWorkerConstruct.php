<?php

namespace Sync\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\config\BeanstalkConfig;
use Sync\Workers\TimeWorker;

class TimeWorkerConstruct extends Command
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
        (new TimeWorker(new BeanstalkConfig()))->execute($input, $output);
        return 0;
    }
}
