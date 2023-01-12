<?php

namespace Sync\Exceptions\Unisender;

use Sync\Exceptions\BaseSyncExceptions;

class AccessDeniedException extends BaseSyncExceptions
{
    protected $message = 'Доступ к API запрещен.';
}
