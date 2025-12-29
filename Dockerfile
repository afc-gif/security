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

# First, verify laravel-app directory exists and list its contents
RUN echo "=== Checking for laravel-app directory ===" && \
    ls -la / | grep -E "var|laravel" && \
    echo "=== Contents of root ===" && \
    ls -la / && \
    echo "=== Contents of /var/www/html ===" && \
    ls -la /var/www/html/

# Copy only composer files first
COPY laravel-app/composer.json /var/www/html/
COPY laravel-app/composer.lock /var/www/html/

# Verify files were copied
RUN echo "=== After copying composer files ===" && \
    ls -la /var/www/html/

# Now copy everything else
COPY laravel-app/ /var/www/html/

# Verify full copy
RUN echo "=== After copying all files ===" && \
    ls -la /var/www/html/ | head -30

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
