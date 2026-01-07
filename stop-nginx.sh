#!/bin/bash

# Nginx Stop Script for ARTSCI POS System
# This script stops Nginx and PHP-FPM

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║         ARTSCI POS System - Nginx Shutdown Script             ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Stop Nginx
echo "🛑 Stopping Nginx..."
if pgrep -x "nginx" > /dev/null; then
    nginx -s quit
    sleep 2
    
    if pgrep -x "nginx" > /dev/null; then
        echo "   ⚠️  Nginx still running, forcing stop..."
        nginx -s stop
    fi
    echo "   ✅ Nginx stopped"
else
    echo "   ℹ️  Nginx is not running"
fi

echo ""
echo "🛑 Stopping PHP-FPM..."
if pgrep -x "php-fpm" > /dev/null; then
    if command -v brew &> /dev/null; then
        # macOS with Homebrew
        brew services stop php
    elif command -v systemctl &> /dev/null; then
        # Linux with systemd
        sudo systemctl stop php-fpm || sudo service php-fpm stop
    fi
    
    sleep 2
    
    if pgrep -x "php-fpm" > /dev/null; then
        echo "   ⚠️  PHP-FPM still running"
    else
        echo "   ✅ PHP-FPM stopped"
    fi
else
    echo "   ℹ️  PHP-FPM is not running"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Shutdown complete!"
echo ""
