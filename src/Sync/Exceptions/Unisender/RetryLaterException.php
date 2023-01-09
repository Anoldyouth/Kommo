<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class RetryLaterException extends BaseSyncExceptions
{
    protected $message = 'Временный сбой.';
}