FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig5 libonig-dev libxml2-dev \
    && docker-php-ext-install dom mbstring mysqli pdo_mysql xml xmlwriter \
    && apt-get purge -y --auto-remove libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /workspace
