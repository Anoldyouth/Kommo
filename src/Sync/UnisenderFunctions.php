<?php

namespace Sync;

use PHPUnit\Util\Exception;
use Unisender\ApiWrapper\UnisenderApi;

class UnisenderFunctions
{
    private string $key;

    public function __construct()
    {
        $this->key = include '.\config\UnisenderConfig.php';
    }

    public function getContact(string $email): array
    {
        $unisenderApi = new UnisenderApi($this->key);
        return json_decode($unisenderApi->getContact(['email' => $email]), true);
    }

    public function manualSync(string $accountName): array
    {
        $contacts = (new ApiService())->getUserContacts($accountName);
        $unisenderApi = new UnisenderApi($this->key);
        $lists = json_decode($unisenderApi->getLists(), true)['result'];
        foreach ($lists as $list) {
            if ($list['title'] == 'Контакты Kommo') {
                $emailListIds = $list['id'];
                break;
            }
        }
        if (!isset($emailListIds)) {
            $emailListIds = json_decode($unisenderApi->createList(['title' => 'Контакты Kommo']), true)['result']['id'];
        }
        $fieldNames = ['email', 'Name', 'email_list_ids'];
        $data = [];
        foreach ($contacts as $contact) {
            if (isset($contact['emails'])) {
                foreach ($contact['emails'] as $email) {
                    $data[] = [$email, $contact['name'], $emailListIds];
                }
            }
        }
        $result = json_decode(
            $unisenderApi->importContacts([
            'field_names' => $fieldNames,
            'data' => $data,
            ]),
            true
        );
        if (isset($result['error'])) {
            throw new Exception();
        }
        $result = $result['result'];
        $logs = $result['log'];
        $result['log'] = [];
        foreach ($logs as $log) {
            $result['log'][] = ['email' => $data[$log['index']][0], 'message' => $log['message']];
        }
        return $result;
    }
}
