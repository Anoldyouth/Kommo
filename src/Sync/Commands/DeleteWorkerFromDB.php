<?php

namespace Sync\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\DatabaseFunctions;
use Sync\Models\Worker;

class DeleteWorkerFromDB extends Command
{
    /**
     * Добавление параметров командной строки
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'Тип воркера',
        );

        $this->addOption(
            'worker',
            'w',
            InputOption::VALUE_REQUIRED,
            'Имя воркера',
        );
    }

    /**
     * Удаление определенного воркера из БД
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $type = $input->getOption('type');
        $name = $input->getOption('worker');
        (new DatabaseFunctions())->deleteWorker($type, $name);
        return 0;
    }
}