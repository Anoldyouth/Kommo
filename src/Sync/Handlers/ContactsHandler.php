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
        $clientId = '6346e956-2f17-4a3f-bcb6-c02a74d52573';
        $clientSecret = 'a48BgXi9tXmbrW1ewozRaoq2J1G4qhrHFAimksl5vhltgi97DT9flO8ZdaOKKAd8';
        $redirectUri = 'https://497a-212-46-197-210.eu.ngrok.io/api';
        $api = new ApiService($clientId, $clientSecret, $redirectUri);
        $arr = $api->getUserContacts($_GET['name']);
        return new JsonResponse([
            'status' => 'ok',
            'data' => $arr
        ]);
    }
}