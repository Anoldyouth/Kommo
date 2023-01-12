<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class InvalidUnisenderKeyException extends BaseSyncExceptions
{
    protected $message = 'Неверный ключ Unisender.';
}
