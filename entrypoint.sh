#!/bin/bash

set -e

bash ./wait-for-it.sh -t 120 ${DB_HOST}:${DB_PORT}

php-fpm
