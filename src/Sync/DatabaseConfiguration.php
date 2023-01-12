<?php

namespace Sync;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Класс для работы с соединениями к базе данных.
 */
class DatabaseConfiguration
{
    /**
     * Возвращает конфигурацию БД для миграции.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return include './config/database.config.php';
    }

    /**
     * Создает соединение с базой данных.
     *
     * @return Capsule
     */
    public function getConnection(): Capsule
    {
        $capsule = new Capsule();
        $capsule->addConnection((include './config/autoload/database.global.php')['database']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }
}
