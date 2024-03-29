## Задача проекта

Необходимо было создать проект, синхронизирующий аккаунты AmoCRM и Unisender.

## Стек проекта

- PHP 7.4
- Фреймворк Mezzio
- Docker
- MySQL
- Beanstalk

## Запуск проекта

После скачивания проекта необходимо сделать следующие вещи:

1) Установка необходимых библиотек:

```bash
   $ composer install
```

2) Создаем .env для подключения к БД:

```dotenv
MYSQL_USER=<user>
MYSQL_PASSWORD=<password>
MYSQL_HOST=application-mysql
MIG_MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DATABASE=<databse>
```

3) Создать образ application-backend:

```bash
$ docker build -t application-backend .
```

4) Запустить проект:

```bash
$ docker compose up
```

## Настройка подключения к виджету AmoCRM

Для того, чтобы проект мог синхронизировать данные, необходимо:

1) Создать свою интеграцию в аккаунте AmoCRM. Заполнить поля следующим образом:
   - Путь переадресации: `<адрес сервера>/auth`
   - Доступ: доступ к данным аккаунта
   - Загрузить виджет с измененным полем `var tokenURL` на путь 
`<адрес сервера>/widget` в файле `script.js`

2) После создания интеграции добавляем данные об интеграции в `/config/ApiClientConfig.php`

3) Добавить адрес сервера `/config/Uri.config.php`

## Возможности проекта
### Путь `/auth?name=<Имя пользователя>`

Позволяет авторизоваться за пользователя с именем name. После разрешения доступа
к данным появится сообщение об авторизации, а в БД появится запись о нем.

### Путь `/contacts?name=<Имя пользователя>`

Позволяет вывести все контакты пользователя.

### Путь `/widget`

Сюда приходит запрос от AmoCRM для подключения виджета к аккаунту. Здесь
добавляется токен Unisender для авторизованного пользователя и подключаются 
вебхукию

### Путь `/webhook`

Сюда приходят вебхуки от AmoCRM при добавлении, изменении и удалении контактов.

### Путь `/contact?email=<email>`

Получение данных об контакте, записанном в Unisender.

### Путь `/sync?name=<Имя пользователя>`

Позволяет вручную синхронизировать контакты с Unisender.

## Автообновление токенов авторизации

Для того, чтобы токены авторизации AmoCRM всегда были действительными, при запуске
docker в cron добавляется запись на ежедневное обновление токенов.

Также в cron добавлена запись на ежедневный запуск скрипта, выполняющий запуск работы
воркера - обработчиков задач на обновление токенов.

### Команды для командной строки
Все команды запускаются из командной строки контейнера application-backend
1) Команда добавления задачи на обновление данных, которые истекают через
заданное время (по умолчанию 24 часа):
```bash
$ /var/www/application/vendor/bin/laminas Sync:update-tokens -t <время>
```
2) Добавление воркера в БД:
```bash
$ /var/www/application/vendor/bin/laminas Sync:add-worker -t <тип> -w <имя> -m <максимальное количество>
```
3) Удаление воркера из БД
```bash
$ /var/www/application/vendor/bin/laminas Sync:delete-worker -t <тип> -w <имя>
```
4) Запуск воркера на отслеживание задач:
```bash
$ /var/www/application/vendor/bin/laminas Sync:start-update-tokens-worker
```
5) Удаление всех записей о воркерах в БД:
```bash
$ /var/www/application/vendor/bin/laminas Sync:clear-workers -t <тип>
```

## Настройка запуска воркеров
В файле `update-tokens` задается таблица для обновления токенов. Там же
запускается скрипт `updating-workers.bash`, отвечающий за работу воркера 
и отслеживающий его состояние в БД.
