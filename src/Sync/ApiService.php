<?php

namespace Sync;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\BadTypeException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\CategoryCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use Laminas\Diactoros\Response\JsonResponse;
use League\OAuth2\Client\Token\AccessToken;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Sync\Exceptions\AmoCRM\ApiException;
use Sync\Exceptions\AmoCRM\AuthApiException;
use Sync\Exceptions\AmoCRM\CreatingButtonException;
use Sync\Exceptions\Base\RandomnessException;
use Sync\Exceptions\BaseSyncExceptions;
use Sync\Exceptions\Unisender\InvalidTokenException;

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
        $client = include '.\config\ApiClientConfig.php';
        $this->apiClient = new AmoCRMApiClient($client['clientId'], $client['clientSecret'], $client['redirectUri']);
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

        // Проверка на авторизацию
        try {
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
            } catch (BadTypeException $ex) {
                throw new CreatingButtonException($ex);
            } catch (Exception $ex) {
                throw new RandomnessException($ex);
            }
        } catch (BaseSyncExceptions $ex) {
            die($ex->getMessage());
        }

        // Записываем токен
        try {
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
            } catch (AmoCRMoAuthApiException $ex) {
                throw new AuthApiException($ex);
            }
        } catch (BaseSyncExceptions $ex) {
            die($ex->getMessage());
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

    /**
     * Получение контактов (имени и рабочих почт).
     *
     * @param string $accountName
     * @return array
     */
    public function getUserContacts(string $accountName): array
    {
        try {
            try {
                // Получаем токен с файла и закрепляем его
                $accessToken = $this->readToken($accountName);
                $this->apiClient->setAccessToken($accessToken);
                $this->apiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);
                $contacts = $this->apiClient->contacts()->get();

                // Проходимся по контактам, собираем имена и рабочие почты
                $result = [];
                foreach ($contacts as $contact) {
                    /** @var ContactModel $contact */
                    if ($contact->getName() !== null) {
                        $emails = [];
                        $fields = $contact->getCustomFieldsValues();
                        foreach ($fields as $field) {
                            /** @var CategoryCustomFieldValuesModel $field */
                            if ($field->getFieldName() == 'Email') {
                                foreach ($field->getValues() as $value) {
                                    /** @var MultitextCustomFieldValueModel $value */
                                    if ($value->getEnum() == 'WORK') {
                                        $emails[] = $value->getValue();
                                    }
                                }
                            }
                        }
                        $result[] = [
                            'name' => $contact->getName(),
                            'emails' => empty($emails) ? null : $emails,
                        ];
                    }
                }
                return $result;
            } catch (AmoCRMMissedTokenException $ex) {
                throw new InvalidTokenException($ex);
            } catch (AmoCRMoAuthApiException $ex) {
                throw new AuthApiException($ex);
            } catch (AmoCRMApiException $ex) {
                throw new ApiException($ex);
            }
        } catch (BaseSyncExceptions $ex) {
            die($ex->getMessage());
        }
    }
}
