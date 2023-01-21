<?php

namespace Sync\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\DatabaseFunctions;
use Sync\Models\Worker;

class AddWorkerToDB extends Command
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

        $this->addOption(
            'max',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Максимальное количество воркеров (по умолчанию 1)',
            1
        );
    }

    /**
     * Добавление записи о воркере в базу данных
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $maxCount = $input->getOption('max');
        $type = $input->getOption('type');
        $name = $input->getOption('worker');
        return (new DatabaseFunctions())->addWorker($type, $name, $maxCount);
    }
}
