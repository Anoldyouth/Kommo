<?php

namespace Sync\Exceptions\AmoCRM;

use Sync\Exceptions\BaseSyncExceptions;

class AuthApiException extends BaseSyncExceptions
{
    protected $message = 'Ошибка авторизации.';
}
