<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\ApiService;

class WidgetHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() == 'POST') {
            $status = (new ApiService())->saveWidgetData($request->getParsedBody());
            return new JsonResponse([
                'status' => $status,
            ], $status);
        }
        return new JsonResponse([
            'status' => 405,
        ], 405);
    }
}
