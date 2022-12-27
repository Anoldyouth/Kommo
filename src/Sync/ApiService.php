<?php

namespace Sync;

use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Token\AccessToken;
use Exception;

class ApiService
{
    /** @var string Базовый домен авторизации. */
    private const TARGET_DOMAIN = 'kommo.com';

    /** @var string Файл хранения токенов. */
    private const TOKENS_FILE = './tokens.json';

    /** @var AmoCRMApiClient AmoCRM клиент. */
    private AmoCRMApiClient $apiClient;

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
    }

    /**
     * Авторизация.
     *
     * @return string
     */
    public function auth(): string
    {
        session_start();

        if (isset($_GET['name'])) {
            $_SESSION['name'] = $_GET['name'];
        }

        if (isset($_GET['referer'])) {
            $this
                ->apiClient
                ->setAccountBaseDomain($_GET['referer'])
                ->getOAuthClient()
                ->setBaseDomain($_GET['referer']);
        }

        try {
            if (!isset($_GET['code'])) {
                $state = bin2hex(random_bytes(16));
                $_SESSION['oauth2state'] = $state;
                if (isset($_GET['button'])) {
                    echo $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getOAuthButton([
                            'title' => 'Установить интеграцию',
                            'compact' => true,
                            'class_name' => 'className',
                            'color' => 'default',
                            'error_callback' => 'handleOauthError',
                            'state' => $state,
                        ]);
                } else {
                    $authorizationUrl = $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getAuthorizeUrl([
                            'state' => $state,
                            'mode' => 'post_message',
                        ]);
                    header('Location: ' . $authorizationUrl);
                }
                die;
            } elseif (
                empty($_GET['state']) ||
                empty($_SESSION['oauth2state']) ||
                ($_GET['state'] !== $_SESSION['oauth2state'])
            ) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        try {
            $accessToken = $this
                ->apiClient
                ->getOAuthClient()
                ->setBaseDomain($_GET['referer'])
                ->getAccessTokenByCode($_GET['code']);

            if (!$accessToken->hasExpired()) {
                $this->saveToken([
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'base_domain' => $this->apiClient->getAccountBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $_SESSION['name'];
    }

    /**
     * Сохранение токена авторизации по имени аккаунта.
     *
     * @param array $token
     * @return void
     */
    private function saveToken(array $token): void
    {
        $tokens = file_exists(self::TOKENS_FILE)
            ? json_decode(file_get_contents(self::TOKENS_FILE), true)
            : [];
        $tokens[$_SESSION['name']] = $token;
        file_put_contents(self::TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    /**
     * Получение токена из файла по имени.
     *
     * @param string $accountName
     * @return AccessToken
     */
    public function readToken(string $accountName): AccessToken
    {
        return new AccessToken(
            json_decode(file_get_contents(self::TOKENS_FILE), true)[$accountName]
        );
    }
}

