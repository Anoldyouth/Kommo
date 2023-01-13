<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\ApiService;

class ContactsHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $api = new ApiService();
        $arr = $api->getUserContacts($_GET['name']);
        return new JsonResponse([
            'status' => 'ok',
            'data' => $arr
        ]);
    }
}