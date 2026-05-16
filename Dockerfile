FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    npm \
    espeak \
    espeak-ng \
    ffmpeg \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .


# Create necessary directories and set permissions
RUN mkdir -p /var/www/bootstrap/cache /var/www/storage /var/www/storage/logs && \
    chmod -R 775 /var/www/bootstrap/cache /var/www/storage && \
    chown -R www-data:www-data /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies
RUN npm install

# Build assets
RUN npm run build
EXPOSE 9000

CMD ["php-fpm"]
