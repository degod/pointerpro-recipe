FROM php:8.2-fpm

# ---------------------------------------------------------
# 1. System dependencies
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git curl libzip-dev zip unzip libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev libxml2-dev libpq-dev default-mysql-client nodejs npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# 2. PHP Extensions
# ---------------------------------------------------------
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

# ---------------------------------------------------------
# 3. Composer
# ---------------------------------------------------------
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# 4. Create app directory & developer user
# ---------------------------------------------------------
RUN useradd -G www-data,root -u 1000 -m developer
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# ---------------------------------------------------------
# 5. PHP configuration overrides
# ---------------------------------------------------------
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# ---------------------------------------------------------
# 6. Permissions
# ---------------------------------------------------------
RUN chown -R developer:www-data /var/www/html
USER developer

# ---------------------------------------------------------
# 7. Expose PHP-FPM port
# ---------------------------------------------------------
EXPOSE 9000
CMD ["php-fpm"]
