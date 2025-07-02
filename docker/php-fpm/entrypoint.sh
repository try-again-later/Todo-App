#!/bin/sh
set -e

APP_ENV=development \
MEMCACHED_SERVERS=${MEMCACHED_HOST}:${MEMCACHED_PORT} \
  php \
    -d variables_order=EGPCS \
    ./migrations.php

exec "$@"
