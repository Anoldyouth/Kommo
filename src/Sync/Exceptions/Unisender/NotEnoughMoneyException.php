<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class NotEnoughMoneyException extends BaseSyncExceptions
{
    protected $message = 'Недостаточно денег на счету.';
}
