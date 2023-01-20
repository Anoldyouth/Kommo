<?php

namespace Sync\AuthData;

use League\OAuth2\Client\Token\AccessToken;

interface AuthData
{
    /**
     * Сохранение токена авторизации.
     *
     * @param array $token
     * @return void
     */
    public function saveAuth(array $token): void;

    /**
     * Получение токена по имени.
     *
     * @param string $accountName
     * @return AccessToken
     */
    public function getAuth(string $accountName): ?AccessToken;
}
