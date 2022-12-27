<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SumHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($request->getQueryParams()) != 2) {
            return new JsonResponse([
                'status' => 'Need 2 number'
            ]);
        }
        return new JsonResponse([
            'status' => 'ok',
            'answer' => array_sum($request->getQueryParams())
        ]);
    }
}