#!/bin/bash

# Docker Stop Script for ARTSCI POS System
# This script stops the Docker containers

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║      ARTSCI POS System - Docker Shutdown Script               ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

LARAVEL_PATH="/home/codecps/Desktop/security/laravel-app"
cd "$LARAVEL_PATH" || exit 1

echo "🛑 Stopping Docker containers..."
echo ""

docker-compose stop

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ All containers stopped successfully"
    echo ""
    echo "📋 Stopped Services:"
    docker-compose ps | tail -n +3 | awk '{print "   ✓ " $1}'
    echo ""
    echo "ℹ️  To start again:"
    echo "   ./docker-start.sh  (quick start)"
    echo "   docker-compose up -d  (manual)"
    echo ""
else
    echo "❌ Failed to stop containers"
    exit 1
fi
