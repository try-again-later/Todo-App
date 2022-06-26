# Todo App

## Run the app inside docker

```sh
git clone https://github.com/try-again-later/Todo-App

cd Todo-App
cp .env.example .env
docker-compose up -d
```

The app will be available at [localhost:8080](http://localhost:8080).

## Run the app locally for development

```sh
git clone https://github.com/try-again-later/Todo-App

cd Todo-App
composer install
cp .env.development .env
docker-compose up -d pgadmin memcached

cd public
APP_ENV=development \
MEMCACHED_SERVERS=127.0.0.1:11211 \
  php \
    -S localhost:8080 \
    -d display_errors=on \
    -d variables_order=EGPCS
```
