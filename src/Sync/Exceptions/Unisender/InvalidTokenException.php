<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class InvalidTokenException extends BaseSyncExceptions
{
    protected $message = 'Невалидный токен авторизации amoCRM.';
}
