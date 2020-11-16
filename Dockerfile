# Dockerfile
FROM php:7.4.1-fpm

ARG ALGOLIA_APP_ID
ARG ALGOLIA_API_KEY
ARG ALGOLIA_APPLICATION_ID_MCM
ARG ALGOLIA_ADMIN_KEY_MCM

ENV ALGOLIA_APP_ID=$ALGOLIA_APP_ID
ENV ALGOLIA_API_KEY=$ALGOLIA_API_KEY
ENV ALGOLIA_APPLICATION_ID_MCM=$ALGOLIA_APPLICATION_ID_MCM
ENV ALGOLIA_ADMIN_KEY_MCM=$ALGOLIA_ADMIN_KEY_MCM

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
