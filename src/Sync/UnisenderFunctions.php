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
        header("Location: https://api.unisender.com/en/api/getContact?format=json&api_key=$this->key&email=$email");
        $platform = 'My E-commerce product v1.0';
        $UnisenderApi = new UnisenderApi($this->key, 'UTF-8', 4, null, false);
        return json_decode($UnisenderApi->getContact(['email' => $email]));
    }
}
