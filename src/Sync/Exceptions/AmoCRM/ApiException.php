<?php

namespace Sync\Exceptions\AmoCRM;

use Sync\Exceptions\BaseSyncExceptions;

class ApiException extends BaseSyncExceptions
{
    protected $message = 'Ошибка получения данных.';
}
