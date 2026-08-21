#!/bin/sh

set -eu

cd /var/www/html

PORT="${PORT:-10000}"

sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

mkdir -p \
    database \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"

    export DB_CONNECTION
    export DB_DATABASE
fi

chown -R www-data:www-data \
    database \
    storage \
    bootstrap/cache

php artisan config:clear

if [ "${BAST_DEMO_RESET:-false}" = "true" ]; then
    echo "Preparing fresh BAST portfolio demo database..."

    if [ "${DB_CONNECTION:-sqlite}" != "sqlite" ]; then
        echo "BAST_DEMO_RESET is only supported with SQLite."
        exit 1
    fi

    rm -f "${DB_DATABASE}"
    touch "${DB_DATABASE}"

    chown www-data:www-data "${DB_DATABASE}"

    php artisan migrate \
        --seed \
        --force
else
    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
        touch "${DB_DATABASE}"
        chown www-data:www-data "${DB_DATABASE}"
    fi

    echo "Running database migrations..."

    php artisan migrate \
        --force
fi

chown -R www-data:www-data \
    database \
    storage \
    bootstrap/cache

exec apache2-foreground