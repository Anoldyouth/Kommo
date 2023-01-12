<?php

namespace Sync;

use Unisender\ApiWrapper\UnisenderApi;

class UnisenderFunctions
{
    private string $key;

    public function __construct()
    {
        $this->key = include '.\config\UnisenderConfig.php';
    }

    public function getContact(string $email)
    {
        $UnisenderApi = new UnisenderApi($this->key);
        return json_decode($UnisenderApi->getContact(['email' => $email]));
    }
}
