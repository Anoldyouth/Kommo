<?php

namespace Sync;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\BadTypeException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\CategoryCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\WebhookModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Client\Token\AccessToken;
use Exception;
use Sync\Exceptions\AmoCRM\ApiException;
use Sync\Exceptions\AmoCRM\AuthApiException;
use Sync\Exceptions\AmoCRM\CreatingButtonException;
use Sync\Exceptions\AmoCRM\EmptyAmoCRMTokenException;
use Sync\Exceptions\AmoCRM\InvalidAmoCRMTokenException;
use Sync\Exceptions\Base\RandomnessException;
use Sync\Exceptions\BaseSyncExceptions;
use Sync\Exceptions\Database\DBModelNotFoundException;
use Sync\Models\Account;
use Sync\Models\UnisenderToken;

class ApiService
{
    /** @var string Базовый домен авторизации. */
    private const TARGET_DOMAIN = 'kommo.com';

    /** @var string Файл хранения токенов. */
    private const TOKENS_FILE = './tokens.json';

    /** @var AmoCRMApiClient AmoCRM клиент. */
    private AmoCRMApiClient $apiClient;

    public function __construct()
    {
        $client = include './config/ApiClientConfig.php';
        $this->apiClient = new AmoCRMApiClient(
            $client['clientId'],
            $client['clientSecret'],
            $client['redirectUri']
        );
    }

    /**
     * Авторизация в AmoCRMApiClient
     *
     * @param string $name
     * @return void
     * @throws EmptyAmoCRMTokenException
     */
    private function authClient(string $name): void
    {
        $accessToken = $this->readToken($name);
        if (empty($accessToken)) {
            throw new EmptyAmoCRMTokenException(new \Exception());
        }
        $this->apiClient->setAccessToken($accessToken);
        $this->apiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);
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
        (new DatabaseFunctions())->getConnection();
        Account::updateOrCreate(
            ['account_name' => $_SESSION['name'],],
            ['access_token' => json_encode($token),]
        );
//        $tokens = file_exists(self::TOKENS_FILE)
//            ? json_decode(file_get_contents(self::TOKENS_FILE), true)
//            : [];
//        $tokens[$_SESSION['name']] = $token;
//        file_put_contents(self::TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    /**
     * Получение токена из файла по имени.
     *
     * @param string $accountName
     * @return AccessToken
     * @throws ModelNotFoundException
     */
    public function readToken(string $accountName): ?AccessToken
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
//        return new AccessToken(
//            json_decode(file_get_contents(self::TOKENS_FILE), true)[$accountName]
//        );
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
                $this->authClient($accountName);
                $contacts = $this->apiClient->contacts()->get();
                // Проходимся по контактам, собираем имена и рабочие почты
                $result = [];
                foreach ($contacts as $contact) {
                    /** @var ContactModel $contact */
                    if ($contact->getName() !== null) {
                        $emails = [];
                        $fields = $contact->getCustomFieldsValues();
                        if (isset($fields)) {
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
                        }
                        $result[] = [
                            'id' => $contact->getId(),
                            'name' => $contact->getName(),
                            'emails' => empty($emails) ? null : $emails,
                        ];
                    }
                }
                return $result;
            } catch (AmoCRMMissedTokenException $ex) {
                throw new InvalidAmoCRMTokenException($ex);
            } catch (AmoCRMoAuthApiException $ex) {
                throw new AuthApiException($ex);
            } catch (AmoCRMApiException $ex) {
                throw new ApiException($ex);
            }
        } catch (BaseSyncExceptions $ex) {
            die($ex->getMessage());
        }
    }

    /**
     * Обработка данных, полученных от виджета
     * Если пользователь в таблице accounts существует,
     * то будет добавлена запись в unisender_tokens и
     * обновлена строка в accounts.
     *
     * @param array $widgetData
     * @return int
     */
    public function saveWidgetData(array $widgetData): int
    {
        if (!(isset($widgetData['Uname']) && isset($widgetData['token']))) {
            return 400;
        }
        try {
            try {
                (new DatabaseFunctions())->getConnection();
                $account = Account::where('account_name', $widgetData['Uname'])->firstOrFail();
                $id = UnisenderToken::firstOrCreate(['token' => $widgetData['token']])->id;
                $account->unisender_token_id = $id;
                $account->save();
                $this->authClient($widgetData['Uname']);
                $webHookModel = (new WebhookModel())
                    ->setSettings([
                        'add_contact',
                        'update_contact',
                        'delete_contact',
                    ])
                    ->setDestination((include './config/Uri.config.php') . '/webhook');
                $this->apiClient
                    ->webhooks()
                    ->subscribe($webHookModel);
                $this->sync($widgetData['Uname']);
                return 200;
            } catch (ModelNotFoundException $ex) {
                throw new DBModelNotFoundException($ex);
            } catch (AmoCRMMissedTokenException $ex) {
                throw new InvalidAmoCRMTokenException($ex);
            } catch (AmoCRMApiException $ex) {
                throw new ApiException($ex);
            } catch (\Throwable $ex) {
                throw new BaseSyncExceptions($ex);
            }
        } catch (BaseSyncExceptions $ex) {
            return 401;
        }
    }


    /**
     * Синхронизация всех контактов аккаунта с Unisender
     *
     * @param string $accountName
     * @return array
     */
    public function sync(string $accountName): array
    {
        $contacts = $this->getUserContacts($accountName);
        $result = [];
        foreach ($contacts as $contact) {
            if (isset($contact['emails'])) {
                $result[] = [
                    'contact id' => $contact['id'],
                    'contact name' => $contact['name'],
                    'contact emails' => $contact['emails'],
                    'result' => (new DatabaseFunctions())->addContactForAccount(
                        $contact,
                        $accountName
                    )
                ];
            }
        }

        return $result;
    }

    /**
     * Синхронизация добавление контакта.
     *
     * @param array $data
     * @return int
     */
    public function addContact(array $data): int
    {
        if (isset($data['name']) && isset($data['custom_fields']) && isset($data['id'])) {
            $emails = [];
            foreach ($data['custom_fields'] as $custom_field) {
                if ($custom_field['code'] == 'EMAIL') {
                    foreach ($custom_field['values'] as $value) {
                        if ($value['enum'] == 369354) {
                            $emails[] = $value['value'];
                        }
                    }
                    break;
                }
            }
            if (!empty($emails)) {
                $contact = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'emails' => $emails,
                ];
                (new DatabaseFunctions())->addContact($contact);
            }
        }
        return 200;
    }

    /**
     * Синхронизация удаления контакта.
     *
     * @param array $data
     * @return int
     */
    public function deleteContact(array $data): int
    {
        if (isset($data['id'])) {
            (new DatabaseFunctions())->deleteContact($data['id']);
        }
        return 200;
    }

    /**
     * Синхронизация обновления контакта.
     *
     * @param array $data
     * @return int
     */
    public function updateContact(array $data): int
    {
        if (isset($data['name']) && isset($data['custom_fields']) && isset($data['id'])) {
            $emails = [];
            foreach ($data['custom_fields'] as $custom_field) {
                if ($custom_field['code'] == 'EMAIL') {
                    foreach ($custom_field['values'] as $value) {
                        if ($value['enum'] == 369354) {
                            $emails[] = $value['value'];
                        }
                    }
                    break;
                }
            }
            if (!empty($emails)) {
                $contact = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'emails' => $emails,
                ];
                (new DatabaseFunctions())->updateContact($contact);
            }
        }
        return 200;
    }
}
