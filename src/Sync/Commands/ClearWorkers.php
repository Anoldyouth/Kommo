<?php

namespace Sync\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\DatabaseFunctions;
use Sync\Models\Worker;

class ClearWorkers extends Command
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
            'Тип воркера для удаления',
        );
    }

    /**
     * Удаление всех воркеров одного типа из БД
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
        (new DatabaseFunctions())->clearWorkers($type);
        return 0;
    }
}
