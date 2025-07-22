FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

COPY composer.json composer.lock /var/www/html/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# تكوين Apache
RUN a2enmod rewrite
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN composer dump-autoload --optimize
