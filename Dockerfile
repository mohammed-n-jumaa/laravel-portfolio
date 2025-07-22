# Use the official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . .

# Install PHP dependencies only if composer.json exists
RUN if [ -f composer.json ]; then \
    composer install --no-interaction --optimize-autoloader --no-dev; \
else \
    echo "No composer.json found, skipping dependency installation"; \
fi

# Create .env file if .env.example exists, otherwise create basic .env
RUN if [ -f .env.example ]; then cp .env.example .env; else \
    echo "APP_NAME=Laravel" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=http://localhost" >> .env && \
    echo "LOG_CHANNEL=stack" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env; \
    fi

# Generate application key only if artisan exists
RUN if [ -f artisan ]; then php artisan key:generate; fi

# Set permissions for Laravel directories if they exist
RUN if [ -d storage ]; then chmod -R 777 storage; fi
RUN if [ -d bootstrap/cache ]; then chmod -R 777 bootstrap/cache; fi

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache for Laravel
RUN echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
