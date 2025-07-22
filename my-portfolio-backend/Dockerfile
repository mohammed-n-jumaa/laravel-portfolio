FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf