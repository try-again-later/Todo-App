# Todo App

## Run the app inside docker

```sh
git clone https://github.com/try-again-later/Todo-App

cd Todo-App
cp .env.local .env
docker-compose up -d --build
```

The app will be available at [localhost:8080](http://localhost:8080).

## Run the app locally for development

```sh
git clone https://github.com/try-again-later/Todo-App

cd Todo-App
composer install
cp .env.development .env
docker-compose up -d pgadmin memcached

# create tables (optionally add CLI parameter "reset" to drop any existing tables)
APP_ENV=development \
MEMCACHED_SERVERS=127.0.0.1:11211 \
  php \
    -d variables_order=EGPCS \
    ./migrations.php

# run tailwind in watch mode
npm i
npm run dev

# run a server with the app
APP_ENV=development \
MEMCACHED_SERVERS=127.0.0.1:11211 \
  php \
    -S localhost:8080 \
    -d display_errors=on \
    -d variables_order=EGPCS \
    -t ./public/
```
