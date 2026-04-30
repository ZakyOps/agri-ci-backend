#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

touch database/database.sqlite

if [ -n "$APP_KEY" ]; then
    if grep -q '^APP_KEY=' .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    else
        printf '\nAPP_KEY=%s\n' "$APP_KEY" >> .env
    fi
else
    php artisan key:generate --force --no-interaction
fi

php artisan config:clear --no-interaction
php artisan migrate:fresh --seed --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
