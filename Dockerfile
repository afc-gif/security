# Use official PHP image with built-in web server
FROM php:8.3-cli-alpine

# Set environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and build tools
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    dpkg-dev \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    zlib-dev \
    && apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    oniguruma \
    freetype \
    libjpeg-turbo \
    libpng

# Install PHP extensions with proper configuration
RUN docker-php-ext-configure gd \
    --with-freetype=/usr \
    --with-jpeg=/usr \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath

# Remove build dependencies to reduce image size
RUN apk del --no-cache .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY laravel-app/ ./

# Create necessary directories
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear any cached configs
RUN rm -rf bootstrap/cache/*.php || true

# Expose port
EXPOSE 8000

# Start the Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
