ARG PHP_VERSION=8.4.27
FROM php:${PHP_VERSION}-cli-bookworm
RUN set -eu; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip libonig-dev libsqlite3-dev libxml2-dev; \
    set --; \
    for phone_extension in mbstring pdo_sqlite dom xml xmlwriter; do \
        if ! php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' "$phone_extension"; then \
            set -- "$@" "$phone_extension"; \
        fi; \
    done; \
    if [ "$#" -gt 0 ]; then docker-php-ext-install -j2 "$@"; fi; \
    rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/tmp/composer
WORKDIR /package
