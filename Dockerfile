FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files from Laravel app
COPY app/composer.json app/composer.lock* ./

# Install dependencies (safe if already vendor exists)
RUN composer install --no-scripts --no-autoloader || true

# Copy the rest of the application
COPY app/ .

# Final composer install (with autoloader)
RUN composer install

# Set permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]

