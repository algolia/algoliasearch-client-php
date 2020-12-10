# Dockerfile
#FROM php:7.3.0-fpm
FROM php:8.0.0-fpm

RUN apt-get update -y \
    && apt-get install -y --no-install-recommends \
        wget \
        zip \
        unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
ADD . /app/

RUN composer install
