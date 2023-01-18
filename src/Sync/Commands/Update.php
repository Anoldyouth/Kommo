<?php

namespace Sync\Commands;

use Illuminate\Support\Carbon;
use Pheanstalk\Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Sync\ApiService;
use Sync\config\BeanstalkConfig;
use Sync\DatabaseFunctions;
use Sync\Exceptions\BaseSyncExceptions;
use Sync\Models\Account;
use Throwable;

class Update extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'time',
            't',
            InputOption::VALUE_OPTIONAL,
            'How long should the token be valid for',
            24
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $time = $input->getOption('time');
            $connection = (new BeanstalkConfig())->getConnection();
            if (!isset($connection)) {
                throw new Exception('Ошибка подключения к Beanstalk');
            }
            $job = $connection
                ->useTube('update')
                ->put($time);
        } catch (Throwable $ex) {
            new BaseSyncExceptions($ex);
        }
        return 0;
    }
}

