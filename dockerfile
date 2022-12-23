# Образ php + fpm + alpine из внешнего репозитория
FROM php:7.4.23-fpm-alpine3.13 as base

# Задаем расположение рабочей директории
ENV WORK_DIR /var/www/application

FROM base

# Указываем, что текущая папка проекта копируется
# в рабочую директорию контейнера
# https://docs.docker.com/engine/reference/builder/#copy
COPY . ${WORK_DIR}

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]