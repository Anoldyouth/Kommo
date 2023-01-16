<?php

namespace Sync\config;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class BeanstalkConfig
{
    /** @var Pheanstalk|null Подключение к серверу очередей */
    private ?Pheanstalk $connection;

    /** @var array Конфигурация подключения */
    private array $config;

    /**
     * Конструктор Beanstalk
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        try {
            if (isset($container)) {
                $this->config = $container->get('config')['beanstalk'];
            } else {
                $this->config = (include './config/beanstalk.local.php')['beanstalk'];
            }
            $this->connection = Pheanstalk::create(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $ex) {
            exit($ex->getMessage());
        }
    }

    /**
     * Возвращает подключение к системе очередей
     *
     * @return Pheanstalk|null
     */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}
