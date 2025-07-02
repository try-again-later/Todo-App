FROM php:8.4-fpm

ARG db_host
ARG db_port
ARG memcached_host
ARG memcached_port

RUN \
  apt update \
  && apt install -y \
    libpq-dev \
    libmemcached-dev \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
  && pecl install \
    memcached-3.2.0 \
  && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    xml \
    zip \
  && docker-php-ext-enable \
    memcached

COPY ./docker/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/
COPY . /var/www

COPY --from=composer:2.2.25 /usr/bin/composer /usr/local/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -i 's/variables_order = .*/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"

RUN chown -R www-data:www-data /var/www

USER www-data

ENV DB_HOST=${db_host}
ENV DB_PORT=${db_port}
ENV MEMCACHED_HOST=${memcached_host}
ENV MEMCACHED_PORT=${memcached_port}

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
