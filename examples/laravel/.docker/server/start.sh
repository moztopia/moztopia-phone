#!/bin/sh
set -eu

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
touch storage/example.sqlite
php artisan migrate --force --no-interaction
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
