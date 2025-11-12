FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libzip-dev zip unzip libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev libxml2-dev libpq-dev default-mysql-client nodejs npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN npm install -g npm@latest

RUN useradd -G www-data,root -u 1000 -m developer
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN chown -R developer:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
