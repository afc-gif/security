# syntax=docker/dockerfile:1

# Builder stage: install PHP extensions and composer, then install dependencies
FROM php:8.3-fpm-bullseye AS builder

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip zip curl libpq-dev libzip-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql gd zip bcmath mbstring pcntl \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Bring in application code
COPY laravel-app/ /var/www/html

# Prepare writable directories
RUN mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --prefer-dist --no-progress --no-interaction \
 && rm -rf /root/.composer


# Runtime stage: nginx + php-fpm
FROM php:8.3-fpm-bullseye

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    nginx curl libpq-dev libzip-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql gd zip bcmath mbstring pcntl \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Nginx configuration for single-container php-fpm
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-custom-pool.conf

# Copy built application from builder
COPY --from=builder /var/www/html /var/www/html

# Default environment (override at runtime)
ENV APP_ENV=production \
    PORT=80

EXPOSE 80

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh \
 && mkdir -p /var/log/nginx \
 && chown -R www-data:www-data storage bootstrap/cache

CMD ["/start.sh"]
