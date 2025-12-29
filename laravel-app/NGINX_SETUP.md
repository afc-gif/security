# Nginx Setup Guide for ARTSCI POS System

This guide covers setting up and running your Laravel app with Nginx instead of the built-in artisan server.

## Prerequisites

- Nginx installed on your system
- PHP-FPM installed and running
- Composer dependencies installed

## Installation

### 1. Install Nginx (if not already installed)

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install nginx
```

**macOS (using Homebrew):**
```bash
brew install nginx
```

**Or download from:** https://nginx.org/en/download.html

### 2. Install PHP-FPM (if not already installed)

**Ubuntu/Debian:**
```bash
sudo apt-get install php-fpm php-mysql php-mbstring php-xml
```

**macOS:**
```bash
brew install php
```

### 3. Start PHP-FPM

**Ubuntu/Debian:**
```bash
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
```

**macOS:**
```bash
brew services start php
```

**Check status:**
```bash
ps aux | grep php-fpm
```

## Running the Laravel App with Nginx

### Quick Start

```bash
cd /home/codecps/Desktop/security/laravel-app

# Make sure composer dependencies are installed
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database and seed sample data
php artisan migrate
php artisan db:seed

# Start Nginx with the provided configuration
nginx -c /home/codecps/Desktop/security/laravel-app/nginx.conf
```

### Access Your App

Open your browser and visit: **http://localhost:8000**

## Configuration Details

The `nginx.conf` file in the project root includes:

- **Server block** listening on port 8000
- **Document root** pointing to `public/` directory
- **PHP handler** using PHP-FPM socket
- **Security headers** (X-Frame-Options, X-Content-Type-Options, etc.)
- **Rewrite rules** for Laravel routing
- **Static file caching** for performance
- **Access/error logging**

## Managing Nginx

### Check if Nginx is running

```bash
ps aux | grep nginx
```

### Stop Nginx

```bash
# Quick stop
nginx -s stop

# Or graceful stop (recommended)
nginx -s quit
```

### Reload Configuration

```bash
nginx -s reload
```

### View access logs

```bash
tail -f /var/log/nginx/laravel_access.log
```

### View error logs

```bash
tail -f /var/log/nginx/laravel_error.log
```

## Troubleshooting

### Port 8000 already in use

If port 8000 is already in use, edit `nginx.conf`:

```nginx
listen 8080;  # Change to another port
```

Then restart:
```bash
nginx -s reload
```

### PHP files being downloaded instead of executed

**Problem:** When accessing the site, PHP files download instead of executing.

**Solution:** Ensure PHP-FPM is running:
```bash
ps aux | grep php-fpm
sudo systemctl start php-fpm  # Ubuntu/Debian
brew services start php       # macOS
```

### "upstream timed out" errors

**Problem:** Requests are timing out.

**Solution:** Increase PHP-FPM timeout in `nginx.conf`:
```nginx
fastcgi_read_timeout 30s;
```

### Permissions issues

**Problem:** 500 errors or blank page.

**Solution:** Ensure proper permissions:
```bash
chmod -R 755 /home/codecps/Desktop/security/laravel-app
chmod -R 777 /home/codecps/Desktop/security/laravel-app/storage
chmod -R 777 /home/codecps/Desktop/security/laravel-app/bootstrap/cache
```

### Cannot access static files

**Problem:** CSS/JS files not loading.

**Solution:** Check that static files are in `public/` directory:
```bash
ls -la /home/codecps/Desktop/security/laravel-app/public/
```

## Advanced Configuration

### Using a different port

Edit `nginx.conf` and change:
```nginx
listen 8000;  # Change this
```

### Using a custom domain (local development)

Edit `/etc/hosts` (or `C:\Windows\System32\drivers\etc\hosts` on Windows):
```
127.0.0.1  laravel.local
```

Then change `nginx.conf`:
```nginx
server_name laravel.local;
listen 80;
```

### Enabling HTTPS for development

Generate self-signed certificate:
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/laravel.key \
  -out /etc/nginx/laravel.crt
```

Add to `nginx.conf`:
```nginx
listen 443 ssl http2;
ssl_certificate /etc/nginx/laravel.crt;
ssl_certificate_key /etc/nginx/laravel.key;
```

### Production-ready Nginx config

For production, consider adding:

```nginx
# Enable gzip compression
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

# Rate limiting
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
location /api/ {
    limit_req zone=api burst=20 nodelay;
}

# Add more security headers
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Content-Security-Policy "default-src 'self'" always;
```

## System Service Setup (Optional)

For automatic startup on reboot, create a systemd service:

**File:** `/etc/systemd/system/nginx-laravel.service`

```ini
[Unit]
Description=Nginx for Laravel POS System
After=network.target php-fpm.service

[Service]
Type=forking
PIDFile=/var/run/nginx.pid
ExecStart=/usr/sbin/nginx -c /home/codecps/Desktop/security/laravel-app/nginx.conf
ExecReload=/bin/kill -s HUP $MAINPID
ExecStop=/bin/kill -s QUIT $MAINPID
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable nginx-laravel
sudo systemctl start nginx-laravel
```

## Default Accounts

After running `php artisan db:seed`:

**Admin:**
- Email: admin@example.com
- Password: admin123

**Test User:**
- Email: john@example.com
- Password: password123

## Next Steps

- Read [SETUP.md](SETUP.md) for general setup
- Read [README.md](README.md) for full documentation
- Check [QUICKREF.md](QUICKREF.md) for quick reference

## Getting Help

For Nginx documentation: https://nginx.org/en/docs/
For Laravel documentation: https://laravel.com/docs
