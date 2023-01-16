<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\UnisenderFunctions;

class ContactHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!empty($request->getQueryParams()['email'])) {
            $text = (new UnisenderFunctions(include './config/UnisenderConfig.php'))
                ->getContact($request->getQueryParams()['email']);
            return new JsonResponse([
                'status' => 'ok',
                'text' => $text,
            ]);
        }
        return new JsonResponse([
            'status' => 'error',
            'text' => 'need email',
        ]);
    }
}
