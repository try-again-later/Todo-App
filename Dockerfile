FROM php:8.1.7-fpm-bullseye

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/

COPY ./composer.* .
RUN composer install

COPY . .
