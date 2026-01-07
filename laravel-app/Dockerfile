# Build stage
FROM php:8.3-fpm-alpine AS builder

WORKDIR /var/www/html
ARG APP_DIR=laravel-app

# Install system dependencies
RUN apk add --no-cache \
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
    git \
    curl \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev

# Install PHP extensions
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

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install dependencies (no-dev for production build)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Copy the rest of the application code
COPY . ./

# Production stage
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install runtime dependencies only
RUN apk add --no-cache \
    freetype \
    libjpeg-turbo \
    libpng \
    oniguruma \
    postgresql-libs \
    git \
    curl

# Install PHP extensions with build dependencies
RUN apk add --no-cache $PHPIZE_DEPS \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    zlib-dev \
    postgresql-dev \
    oniguruma-dev && \
    docker-php-ext-configure gd \
    --with-freetype=/usr \
    --with-jpeg=/usr \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath && \
    apk del $PHPIZE_DEPS \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    zlib-dev \
    postgresql-dev \
    oniguruma-dev

# Copy from builder
COPY --from=builder /var/www/html /var/www/html

# Create storage directories
RUN mkdir -p storage bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
