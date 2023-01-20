<?php

namespace Sync\Workers;

use Symfony\Component\Console\Output\OutputInterface;

class TimeWorker extends BaseWorker
{
    /** @var string Просматриваемая очередь. */
    protected string $queue = 'times';

    /** Обработка задачи */
    public function process($data, OutputInterface $output): void
    {
        $output->writeln($data);
    }
}
