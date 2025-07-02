# Todo App

## Deploy for production

```sh
git clone https://github.com/try-again-later/Todo-App
cd Todo-App

# Set APP_ENV to "production"
cp .env.local .env
docker-compose up -d --build
```

The app will be available at [localhost](http://localhost).

## Run locally for development

```sh
git clone https://github.com/try-again-later/Todo-App
cd Todo-App

cp .env.development .env
docker-compose up -d todo-app-postgres todo-app-memcached

# create tables
composer install
composer run migrate:fresh

# run tailwind in watch mode
npm i
npm run dev

# run a server with the app
composer run dev
```

The app will be available at [localhost:8080](http://localhost:8080).
