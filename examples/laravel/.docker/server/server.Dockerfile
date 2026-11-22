FROM php:8.4.27-cli-alpine AS builder

RUN set -eu; \
    apk add --no-cache git unzip oniguruma-dev sqlite-dev $PHPIZE_DEPS; \
    set --; \
    for phone_extension in mbstring pdo_sqlite; do \
        if ! php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' "$phone_extension"; then \
            set -- "$@" "$phone_extension"; \
        fi; \
    done; \
    if [ "$#" -gt 0 ]; then docker-php-ext-install -j2 "$@"; fi

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json README.md AGENTS.md LICENSE NOTICE VERSION CHANGELOG.md CONTRIBUTING.md CODE_OF_CONDUCT.md SECURITY.md /package/
COPY src /package/src
COPY lang /package/lang
COPY docs /package/docs
COPY examples/laravel /package/examples/laravel
COPY examples/laravel /app

WORKDIR /app
ENV APP_ENV=local APP_DEBUG=false LOG_CHANNEL=stderr COMPOSER_ALLOW_SUPERUSER=1

RUN set -eu; \
    mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs; \
    composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; \
    chown -R www-data:www-data bootstrap/cache

FROM php:8.4.27-cli-alpine
RUN apk add --no-cache oniguruma sqlite-libs
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
COPY --from=builder /app /app
WORKDIR /app
ENV APP_ENV=local APP_DEBUG=false LOG_CHANNEL=stderr
USER www-data
EXPOSE 8000
CMD ["sh", ".docker/server/start.sh"]
