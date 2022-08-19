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

cp .env.development .env
docker-compose up -d pgadmin memcached

# create tables
composer install
composer run migrate:fresh

# run tailwind in watch mode
npm i
npm run dev

# run a server with the app
composer run dev
```
