<?php

namespace Sync;

use Illuminate\Database\Capsule\Manager as Capsule;
use Sync\Exceptions\BaseSyncExceptions;
use Sync\Models\Account;
use Sync\Models\Contact;
use Sync\Models\UnisenderToken;
use Sync\Models\Worker;

/**
 * Класс для работы с соединениями к базе данных.
 */
class DatabaseFunctions
{
    /**
     * Возвращает конфигурацию БД для миграции.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return include './config/database.config.php';
    }

    /**
     * Создает соединение с базой данных.
     *
     * @return Capsule
     */
    public function getConnection(): Capsule
    {
        $capsule = new Capsule();
        $capsule->addConnection((include './config/autoload/database.global.php')['database']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }

    /**
     * Получение токена Unisender.
     *
     * @param string $accountName
     * @return string
     */
    public function getToken(string $accountName): string
    {
        $this->getConnection();
        return Account::where('account_name', $accountName)
            ->first()
            ->token
            ->token;
    }

    /**
     * Добавление одного контакта для одного аккаунта.
     *
     * @param array $contact
     * @param string $accountName
     * @return string[]
     */
    public function addContactForAccount(array $contact, string $accountName): array
    {
        $this->getConnection();
        $token = $this->getToken($accountName);
        if (!empty($contact['emails'])) {
            $result = (new UnisenderFunctions($token))
                ->addContact($accountName, $contact);
            $contact = $result['added'];
        } else {
            return ['error' => 'Нет контактов для синхронизации.'];
        }
        foreach ($contact['emails'] as $email) {
            Contact::updateOrCreate([
                'work_email' => $email,
            ], [
                'kommo_contact_id' => $contact['id'],
                'name' => $contact['name'],
            ]);
        }
        return $result['result'];
    }

    /**
     * Добавление контакта в БД для всех аккаунтов.
     *
     * @param array $contact
     * @return void
     */
    public function addContact(array $contact): void
    {
        $this->getConnection();
        $tokens = UnisenderToken::get();
        foreach ($tokens as $token) {
            $accounts = $token->accounts;
            foreach ($accounts as $account) {
                if (!empty($contact['emails'])) {
                    $contact = (new UnisenderFunctions($token->token))
                        ->addContact($account->account_name, $contact)['added'];
                } else {
                    return;
                }
            }
        }
        foreach ($contact['emails'] as $email) {
            Contact::updateOrCreate([
                'work_email' => $email,
            ], [
                'kommo_contact_id' => $contact['id'],
                'name' => $contact['name'],
            ]);
        }
    }

    /**
     * Удаление контакта из БД по id для всех аккаунтов.
     *
     * @param string $id
     * @return void
     */
    public function deleteContact(string $id): void
    {
        $this->getConnection();
        $models = Contact::where('kommo_contact_id', '=', $id)->get();
        $emails = [];
        foreach ($models as $model) {
            $emails[] = $model->work_email;
        }
        if (!empty($emails)) {
            $contact = [
                'name' => $models[0]->name,
                'emails' => $emails,
            ];
            $tokens = UnisenderToken::all();
            foreach ($tokens as $token) {
                $accounts = $token->accounts;
                foreach ($accounts as $account) {
                    (new UnisenderFunctions($token->token))
                        ->deleteContact($account->account_name, $contact);
                }
            }
            foreach ($models as $model) {
                $model->delete();
            }
        }
    }

    /**
     * Обновление контакта (удаление + добавление) в БД для всех аккаунтов.
     *
     * @param array $updatedContact
     * @return void
     */
    public function updateContact(array $updatedContact)
    {
        $this->getConnection();
        $models = Contact::where(
            'kommo_contact_id',
            '=',
            $updatedContact['id']
        )->get();
        $emails = [];
        foreach ($models as $model) {
            $emails[] = $model->work_email;
        }

        $deleted = [
            'id' => $updatedContact['id'],
            'name' => $models->first()->name,
            'emails' => array_diff($emails, $updatedContact['emails']),
        ];

        $added = [
            'id' => $updatedContact['id'],
            'name' => $models->first()->name,
            'emails' => array_diff($updatedContact['emails'], $emails),
        ];

        if (empty($deleted['emails']) && empty($added['emails'])) {
            return;
        }

        $tokens = UnisenderToken::all();
        foreach ($tokens as $token) {
            $accounts = $token->accounts;
            foreach ($accounts as $account) {
                if (empty($deleted['emails']) && empty($added['emails'])) {
                    return;
                }
                $added = (new UnisenderFunctions($token->token))
                    ->updateContact($account->account_name, $deleted, $added)['added'];
            }
        }
        if (!empty($deleted['emails'])) {
            Contact::whereIn('work_email', $deleted['emails'])->delete();
        }
        if (!empty($added['emails'])) {
            foreach ($added['emails'] as $email) {
                Contact::updateOrCreate([
                    'work_email' => $email,
                ], [
                    'kommo_contact_id' => $added['id'],
                    'name' => $added['name'],
                ]);
            }
        }
    }

    public function deleteToken(string $accountName): void
    {
        $this->getConnection();
        Account::where('account_name', $accountName)->update(['access_token' => null]);
    }

    public function addWorker(string $type, string $workerName, int $maxCount): int
    {
        $this->getConnection();
        if (Worker::where('type', $type)->count() >= $maxCount) {
            return -1;
        }

        Worker::updateOrCreate([
            'type' => $type,
            'name' => $workerName,
        ]);
        return 0;
    }

    public function deleteWorker(string $type, string $workerName): void
    {
        $this->getConnection();
        Worker::where('type', $type)->where('name', $workerName)->delete();
    }

    public function clearWorkers(string $type): void
    {
        $this->getConnection();
        Worker::where('type', $type)->delete();
    }
}
