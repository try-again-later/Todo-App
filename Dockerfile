FROM debian:bookworm-slim AS builder

RUN \
  apt update \
  && apt install -y \
    git

RUN git clone https://github.com/vishnubob/wait-for-it /tmp/wait-for-it/
RUN chmod +x /tmp/wait-for-it/wait-for-it.sh


FROM php:8.1.7-fpm-bullseye

ARG db_host
ARG db_port

RUN \
  apt update \
  && apt install -y \
    libpq-dev

RUN docker-php-ext-install \
  pdo \
  pdo_pgsql \
  pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/

COPY ./composer.* .
RUN composer install

COPY . .
COPY --from=builder /tmp/wait-for-it/wait-for-it.sh /var/www/wait-for-it.sh

RUN chmod +x /var/www/entrypoint.sh
ENV DB_HOST=${db_host}
ENV DB_PORT=${db_port}
ENTRYPOINT ["/var/www/entrypoint.sh"]
