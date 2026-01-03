# Use official PHP-FPM image
FROM php:8.3-fpm-alpine

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

# Copy dependency manifests first (cache friendly)
COPY composer.json composer.lock* ./

# Copy application files
COPY . ./

# Create necessary directories
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear any cached configs
RUN rm -rf bootstrap/cache/*.php || true

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
