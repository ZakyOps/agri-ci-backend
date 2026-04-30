#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

touch database/database.sqlite

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi

php artisan migrate:fresh --seed --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
