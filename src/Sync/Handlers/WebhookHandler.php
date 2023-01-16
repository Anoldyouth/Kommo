<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\ApiService;

class WebhookHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() == 'POST') {
            if (isset($request->getParsedBody()['contacts'])) {
                $data = $request->getParsedBody()['contacts'];
                if (isset($data['add'])) {
                    $status = (new ApiService())->addContact($data['add'][0]);
                } elseif (isset($data['update'])) {
                    $status = (new ApiService())->updateContact($data['update'][0]);
                } elseif (isset($data['delete'])) {
                    $status = (new ApiService())->deleteContact($data['delete'][0]);
                } else {
                    $status = 400;
                }
            } else {
                $status = 400;
            }
        } else {
            $status = 405;
        }
        return new JsonResponse([
            'status' => $status,
        ], $status);
    }
}
