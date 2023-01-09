<?php

namespace Sync\Exceptions\AmoCRM;

use Sync\Exceptions\BaseSyncExceptions;

class InvalidAmoCRMTokenException extends BaseSyncExceptions
{
    protected $message = 'Невалидный токен авторизации amoCRM.';
}
