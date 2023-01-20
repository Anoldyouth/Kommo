<?php

namespace Sync\Workers;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\config\BeanstalkConfig;
use Throwable;

abstract class BaseWorker extends Command
{
    /** @var Pheanstalk Текущее подключение к серверу очередей. */
    protected Pheanstalk $connection;

    /** @var string Просматриваемая очередь. */
    protected string $queue = 'default';

    /**
     * Конструктор
     *
     * @param BeanstalkConfig $beanstalk
     */
    final public function __construct(BeanstalkConfig $beanstalk)
    {
        parent::__construct();
        $this->connection = $beanstalk->getConnection();
    }

    /**
     * Вызов через CLI
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Job $job */
        while (
            $job = $this->connection
            ->watchOnly($this->queue)
            ->ignore(PheanstalkInterface::DEFAULT_TUBE)
            ->reserve()
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ), $output);
            } catch (Throwable $ex) {
                $this->handleException($ex, $job, $output);
            }

            $this->connection->delete($job);
        }
    }

    /**
     * Вывод ошибок
     *
     * @param Throwable $ex
     * @param Job $job
     * @param OutputInterface $output
     * @return void
     */
    private function handleException(Throwable $ex, Job $job, OutputInterface $output): void
    {
        $output->writeln("<error>Error Unhandled exception $ex" . PHP_EOL . $job->getData() . "<error>");
    }

    /**
     * Действия, исполняемые при событии
     *
     * @param $data
     * @param OutputInterface $output
     * @return mixed
     */
    abstract public function process($data, OutputInterface $output);
}
