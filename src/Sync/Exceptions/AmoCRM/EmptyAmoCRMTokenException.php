<?php

namespace Sync\Exceptions\AmoCRM;

use Sync\Exceptions\BaseSyncExceptions;

class EmptyAmoCRMTokenException extends BaseSyncExceptions
{
    protected $message = 'Нет авторизации.';
}
