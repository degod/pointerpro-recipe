# ===== STAGE 1: Build PHP (slim, fast) =====
FROM php:8.2-fpm-alpine AS php-builder

# Install system deps
RUN apk add --no-cache \
    git curl zip unzip libpng libjpeg-turbo freetype libzip \
    oniguruma icu libxml2 postgresql-libs mysql-client \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev icu-dev libxml2-dev postgresql-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ===== STAGE 2: Runtime (minimal) =====
FROM php:8.2-fpm-alpine

# Copy PHP extensions + config
COPY --from=php-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-builder /usr/local/etc/php/ /usr/local/etc/php/
COPY --from=php-builder /usr/bin/composer /usr/bin/composer

# Create user
RUN adduser -D -u 1000 -G www-data developer \
    && mkdir -p /var/www/html \
    && chown developer:www-data /var/www/html

WORKDIR /var/www/html

# COPY CODE + INSTALL COMPOSER DEPS
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# Run Laravel post-install
RUN php artisan storage:link \
    && php artisan route:cache \
    && php artisan config:cache \
    && php artisan view:cache

# PHP config: opcache + performance
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]