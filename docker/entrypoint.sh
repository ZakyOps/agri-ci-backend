#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

touch database/database.sqlite

php artisan key:generate --force --no-interaction
php artisan migrate:fresh --seed --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port=8000
