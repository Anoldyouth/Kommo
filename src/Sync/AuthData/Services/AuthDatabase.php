<?php

namespace Sync\AuthData\Services;

use League\OAuth2\Client\Token\AccessToken;
use Sync\AuthData\AuthData;
use Sync\DatabaseFunctions;
use Sync\Models\Account;

class AuthDatabase implements AuthData
{
    /**
     * Сохранение токена в БД
     *
     * @param array $token
     * @return void
     */
    public function saveAuth(array $token): void
    {
        (new DatabaseFunctions())->getConnection();
        Account::updateOrCreate(
            ['account_name' => $_SESSION['name'],],
            ['access_token' => json_encode($token),]
        );
    }

    /**
     * Получение токена из БД
     *
     * @param string $accountName
     * @return AccessToken|null
     */
    public function getAuth(string $accountName): ?AccessToken
    {
        (new DatabaseFunctions())->getConnection();
        return new AccessToken(
            json_decode(
                Account::where('account_name', '=', $accountName)
                    ->firstOrFail()
                    ->access_token,
                true
            )
        );
    }
}