<?php

declare(strict_types=1);

namespace Sync\Handlers;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\ApiService;

class ApiHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = (new ApiService())->auth();
        return new JsonResponse([
            'status' => 'ok',
            'name' => $name
        ]);
    }
}