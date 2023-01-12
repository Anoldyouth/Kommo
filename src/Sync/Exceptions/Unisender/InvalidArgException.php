<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class InvalidArgException extends BaseSyncExceptions
{
    protected $message = 'Неправильный формат почты.';
}
