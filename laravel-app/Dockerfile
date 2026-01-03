# Build stage
FROM php:8.3-fpm-alpine AS builder

WORKDIR /var/www/html

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

# Copy app files from laravel-app directory
COPY laravel-app/ ./

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Production stage
FROM php:8.3-cli-alpine

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

EXPOSE 8000

# Run migrations on container start (non-fatal if DB not yet reachable), then serve
CMD ["sh", "-c", "php artisan migrate --force || true && php artisan serve --host=0.0.0.0 --port=8000"]
