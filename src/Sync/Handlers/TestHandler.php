<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Carbon\Carbon;
use DateTimeZone;
use Laminas\Diactoros\Response\JsonResponse;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\config\BeanstalkConfig;

class TestHandler implements RequestHandlerInterface
{
    /** @var ContainerInterface Контейнер */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $job = (new BeanstalkConfig($this->container))->getConnection()
            ->useTube('times')
            ->put(json_encode(sprintf(
                "Now time: %s",
                Carbon::now(new DateTimeZone('Europe/Moscow'))->isoFormat('HH:mm (DD.YYYY)')
            )));
        return new JsonResponse([
            'status' => 'ok',
            'id' => $job->getId(),
        ]);
    }
}
