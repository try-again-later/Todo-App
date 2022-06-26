#!/bin/bash

set -e

bash ./wait-for-it.sh -t 120 ${DB_HOST}:${DB_PORT}
bash ./wait-for-it.sh -t 120 ${MEMCACHED_HOST}:${MEMCACHED_PORT}

php-fpm
