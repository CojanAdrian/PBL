# Local preview only (Railway builds the app with Railpack, not this file).
FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libicu-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql intl zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
