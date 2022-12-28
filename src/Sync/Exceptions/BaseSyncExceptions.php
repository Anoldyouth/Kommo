<?php

namespace Sync\Exceptions;

use Exception;
use Hopex\Simplog\Logger;

class BaseSyncExceptions extends Exception
{
    public function __construct(Exception $originalException = null)
    {
        parent::__construct();
        $this->report($originalException);
    }

    /**
     * Сохранение данных об исключениях.
     *
     * @param Exception $originalException
     * @return void
     */
    private function report(Exception $originalException): void
    {
        (new Logger())
            ->setLevel('exceptions')
            ->setDateFormat('H:i:s')
            ->setTimeZone('Europe/Moscow')
            ->exception(
                $originalException,
                false,
                [
                    'view-message' => $this->getMessage()
                ]
            );
    }
}
