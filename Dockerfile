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

# Create public directory if it doesn't exist and add basic index.php
RUN if [ ! -d "public" ]; then \
    mkdir -p public && \
    echo "<?php phpinfo(); ?>" > public/index.php; \
fi

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

# Create Laravel directories if they don't exist
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# Set permissions for Laravel directories
RUN chmod -R 777 storage bootstrap/cache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache for Laravel with fallback
RUN echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php index.html\n\
        \n\
        # Laravel pretty URLs\n\
        <IfModule mod_rewrite.c>\n\
            RewriteEngine On\n\
            RewriteCond %{REQUEST_FILENAME} !-f\n\
            RewriteCond %{REQUEST_FILENAME} !-d\n\
            RewriteRule ^(.*)$ index.php/$1 [L]\n\
        </IfModule>\n\
    </Directory>\n\
    \n\
    # Health check endpoint\n\
    Alias /api/health /var/www/html/health.php\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Create a simple health check file
RUN echo '<?php\nheader("Content-Type: application/json");\necho json_encode(["status" => "ok", "timestamp" => date("c")]);\n?>' > /var/www/html/health.php

# Create a simple fallback index.php in public if Laravel index doesn't exist
RUN if [ ! -f "public/index.php" ]; then \
    echo '<?php\necho "<h1>Laravel Application</h1>";\necho "<p>Application is running!</p>";\necho "<p>Time: " . date("Y-m-d H:i:s") . "</p>";\n?>' > public/index.php; \
fi

# Debug: List directory contents
RUN ls -la /var/www/html/ && echo "--- Public directory ---" && ls -la /var/www/html/public/

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
