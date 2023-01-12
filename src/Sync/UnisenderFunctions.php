<?php

namespace Sync;

use Hopex\Simplog\Logger;
use Sync\Exceptions\BaseSyncExceptions;
use Sync\Exceptions\Unisender\AccessDeniedException;
use Sync\Exceptions\Unisender\InvalidArgException;
use Sync\Exceptions\Unisender\InvalidUnisenderKeyException;
use Sync\Exceptions\Unisender\NotEnoughMoneyException;
use Sync\Exceptions\Unisender\RetryLaterException;
use Sync\Exceptions\Unisender\UnisenderException;
use Unisender\ApiWrapper\UnisenderApi;

class UnisenderFunctions
{
    /** @var string ключ Unisender. */
    private string $key;

    public function __construct()
    {
        $this->key = include './config/UnisenderConfig.php';
    }

    /**
     * Получение от Unisender данных о почте, переданной в функцию.
     *
     * @param string $email
     * @return array
     */
    public function getContact(string $email): array
    {
        $unisenderApi = new UnisenderApi($this->key);
        return $this->checkingAnswer(json_decode($unisenderApi->getContact(['email' => $email]), true))['result'];
    }

    /**
     * Метод, используемый для ручной синхронизации.
     * При необходимости, создает новый лист в аккаунте Unisender,
     * а затем копирует все контакты из Kommo в Unisender.
     *
     * @param string $accountName
     * @return array
     */
    public function manualSync(string $accountName): array
    {
        // Получение контактов AmoCRM
        $contacts = (new ApiService())->getUserContacts($accountName);
        $unisenderApi = new UnisenderApi($this->key);

        // Проверка существования листа
        $lists = $this->checkingAnswer(json_decode($unisenderApi->getLists(), true))['result'];
        foreach ($lists as $list) {
            if ($list['title'] == 'Контакты Kommo') {
                $emailListIds = $list['id'];
                break;
            }
        }

        //Если лист с таким именем не найден, то создаем новый
        if (!isset($emailListIds)) {
            $emailListIds = $this->checkingAnswer(
                json_decode($unisenderApi->createList(['title' => 'Контакты Kommo']), true)
            )['result']['id'];
        }

        // Создаем посылку для Unisender
        $fieldNames = ['email', 'Name', 'email_list_ids'];
        $data = [];
        // TODO: группировать отправки по 500 контактов
        foreach ($contacts as $contact) {
            if (isset($contact['emails'])) {
                foreach ($contact['emails'] as $email) {
                    $data[] = [$email, $contact['name'], $emailListIds];
                }
            }
        }
        $result = $this->checkingAnswer(json_decode(
            $unisenderApi->importContacts([
            'field_names' => $fieldNames,
            'data' => $data,
            ]),
            true
        ))['result'];

        // Обработка предупреждений от Unisender
        $logs = $result['log'];
        $result['log'] = [];
        $logWarnings = [
            'accountName' => $accountName,
        ];
        foreach ($logs as $log) {
            $result['log'][] = [
                'email' => $data[$log['index']][0],
                'message' => $log['message'],
            ];
        }
        $logWarnings['logs'] = $result['log'];
        (new Logger())
            ->setLevel('other')
            ->setDirectoryPermissions(0775)
            ->warning($logWarnings);

        return $result;
    }

    /**
     * Проверка ответа Unisender на сообщение об ошибке.
     *
     * @param array $answer
     * @return array
     */
    private function checkingAnswer(array $answer): array
    {
        try {
            try {
                if (isset($answer['error'])) {
                    if (preg_match('(Contact not found)', $answer['error']) > 0) {
                        $answer['result'] = ['error' => 'Контакт не найден.'];
                        return $answer;
                    }
                    throw new \Exception($answer['error']);
                }
            } catch (\Exception $e) {
                switch ($answer['code']) {
                    case 'invalid_api_key':
                        throw new InvalidUnisenderKeyException($e);
                    case 'access_denied':
                        throw new AccessDeniedException($e);
                    case 'not_enough_money':
                        throw new NotEnoughMoneyException($e);
                    case 'retry_later':
                        throw new RetryLaterException($e);
                    case 'invalid_arg':
                        throw new InvalidArgException($e);
                    default:
                        throw new UnisenderException($e);
                }
            }
        } catch (BaseSyncExceptions $e) {
            die($e->getMessage());
        }
        return $answer;
    }
}
