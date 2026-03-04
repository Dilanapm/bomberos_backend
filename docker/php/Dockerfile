FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash curl git unzip \
    libzip-dev oniguruma-dev icu-dev \
    postgresql-dev postgresql-client \
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo pdo_pgsql mbstring zip intl

RUN apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd

RUN pecl install redis \
 && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D -u 1000 www

USER www

