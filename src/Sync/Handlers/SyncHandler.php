<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\UnisenderFunctions;

class SyncHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($request->getQueryParams()['name'])) {
            return new JsonResponse([
                'status' => 'error',
                'text' => 'need name',
            ]);
        }
        $text = (new UnisenderFunctions())->manualSync($request->getQueryParams()['name']);
        return new JsonResponse([
            'status' => 'ok',
            'text' => $text,
        ]);
    }
}
