<?php

namespace Sync\AuthData\Services;

use League\OAuth2\Client\Token\AccessToken;
use Sync\AuthData\AuthData;

class AuthFile implements AuthData
{
    /** @var string Файл хранения токенов. */
    private const TOKENS_FILE = './tokens.json';

    public function saveAuth(array $token): void
    {
        $tokens = file_exists(self::TOKENS_FILE)
            ? json_decode(file_get_contents(self::TOKENS_FILE), true)
            : [];
        $tokens[$_SESSION['name']] = $token;
        file_put_contents(self::TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    public function getAuth(string $accountName): ?AccessToken
    {
        return new AccessToken(
            json_decode(file_get_contents(self::TOKENS_FILE), true)[$accountName]
        );
    }
}
