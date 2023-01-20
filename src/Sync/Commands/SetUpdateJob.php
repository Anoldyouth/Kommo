<?php

namespace Sync\Commands;

use Illuminate\Support\Carbon;
use Pheanstalk\Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Pheanstalk\Pheanstalk;
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

class SetUpdateJob extends Command
{
    /** @var Pheanstalk Текущее подключение к серверу очередей. */
    protected Pheanstalk $connection;

    /**
     * Создаем подключение к Beanstalk
     *
     * @param BeanstalkConfig $beanstalkConfig
     */
    public function __construct(BeanstalkConfig $beanstalkConfig)
    {
        parent::__construct();
        $this->connection = $beanstalkConfig->getConnection();
    }

    /**
     * Установка параметров командной строки
     *
     * @return void
     */
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

    /**
     * Добавление задачи в очередь
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $time = $input->getOption('time');
            if (!isset($this->connection)) {
                throw new Exception('Ошибка подключения к Beanstalk');
            }
            $job = $this->connection
                ->useTube('update')
                ->put($time);
        } catch (Throwable $ex) {
            new BaseSyncExceptions($ex);
        }
        return 0;
    }
}

