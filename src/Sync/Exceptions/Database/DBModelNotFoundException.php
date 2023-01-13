<?php

namespace Sync\Exceptions\Database;

use Sync\Exceptions\BaseSyncExceptions;

class DBModelNotFoundException extends BaseSyncExceptions
{
    protected $message = 'Модель не найдена.';
}
