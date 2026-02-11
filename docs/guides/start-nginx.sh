#!/bin/bash

# Nginx Startup Script for ARTSCI POS System
# This script starts PHP-FPM and Nginx

LARAVEL_PATH="/home/codecps/security"
NGINX_CONFIG="$LARAVEL_PATH/nginx.conf"

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║         ARTSCI POS System - Nginx Startup Script              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Check if Laravel path exists
if [ ! -d "$LARAVEL_PATH" ]; then
    echo "❌ Laravel path not found: $LARAVEL_PATH"
    exit 1
fi

echo "📁 Laravel Path: $LARAVEL_PATH"
echo ""

# Start PHP-FPM
echo "🚀 Checking PHP-FPM..."
if ! pgrep -x "php-fpm" > /dev/null; then
    echo "   Starting PHP-FPM..."
    if command -v brew &> /dev/null; then
        # macOS with Homebrew
        brew services start php
    elif command -v systemctl &> /dev/null; then
        # Linux with systemd
        sudo systemctl start php-fpm || sudo service php-fpm start
    fi
    sleep 2
else
    echo "   ✅ PHP-FPM is already running"
fi

echo ""
echo "🌐 Starting Nginx..."

# Check if Nginx is already running
if pgrep -x "nginx" > /dev/null; then
    echo "   ⚠️  Nginx is already running. Reloading config..."
    nginx -s reload
else
    echo "   Starting new Nginx instance..."
    nginx -c "$NGINX_CONFIG"
fi

sleep 2

# Verify services are running
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if pgrep -x "php-fpm" > /dev/null; then
    echo "✅ PHP-FPM is running"
else
    echo "❌ PHP-FPM failed to start"
    exit 1
fi

if pgrep -x "nginx" > /dev/null; then
    echo "✅ Nginx is running"
else
    echo "❌ Nginx failed to start"
    exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 All services started successfully!"
echo ""
echo "📍 Visit: http://localhost:8000"
echo ""
echo "🔑 Default Accounts:"
echo "   Admin:  admin@example.com / admin123"
echo "   User:   john@example.com / password123"
echo ""
echo "🛑 To stop services:"
echo "   nginx -s stop"
echo "   brew services stop php  (macOS)"
echo "   sudo systemctl stop php-fpm  (Linux)"
echo ""
echo "📖 For more info, see NGINX_SETUP.md"
echo ""
