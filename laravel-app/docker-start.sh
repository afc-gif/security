#!/bin/bash

# Docker Quick Start Script for ARTSCI POS System
# This script initializes and starts the Docker containers

LARAVEL_PATH="/home/codecps/security/laravel-app"

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║      ARTSCI POS System - Docker Startup Script                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker Desktop."
    echo "   Visit: https://www.docker.com/products/docker-desktop"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed."
    exit 1
fi

echo "✅ Docker and Docker Compose found"
echo ""

# Navigate to project directory
cd "$LARAVEL_PATH" || exit 1

echo "📁 Working directory: $LARAVEL_PATH"
echo ""

# Check if Docker daemon is running
echo "🔍 Checking Docker daemon..."
if ! docker ps &> /dev/null; then
    echo "❌ Docker daemon is not running. Please start Docker Desktop."
    exit 1
fi
echo "✅ Docker daemon is running"
echo ""

# Setup .env file if it doesn't exist
if [ ! -f "$LARAVEL_PATH/.env" ]; then
    echo "📝 Creating .env file..."
    cp "$LARAVEL_PATH/.env.example" "$LARAVEL_PATH/.env"
    
    # Update database connection for Docker
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' "$LARAVEL_PATH/.env"
    sed -i 's/DB_HOST=.*/DB_HOST=mysql/' "$LARAVEL_PATH/.env"
    sed -i 's/DB_PORT=.*/DB_PORT=3306/' "$LARAVEL_PATH/.env"
    sed -i 's/REDIS_HOST=.*/REDIS_HOST=redis/' "$LARAVEL_PATH/.env"
    echo "✅ .env file created with Docker database settings"
else
    echo "✅ .env file already exists"
fi

echo ""
echo "🚀 Building and starting Docker containers..."
echo "   (This may take a few minutes on first run)"
echo ""

docker-compose up -d

if [ $? -ne 0 ]; then
    echo "❌ Failed to start Docker containers"
    exit 1
fi

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 8

# Check service health
echo ""
echo "🔍 Checking service health..."

# Wait for MySQL to be ready
echo "   Waiting for MySQL..."
for i in {1..30}; do
    docker-compose exec -T mysql mysqladmin ping -h localhost &> /dev/null
    if [ $? -eq 0 ]; then
        echo "   ✅ MySQL is ready"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "   ⚠️  MySQL may still be starting..."
    fi
    sleep 1
done

# Run migrations
echo ""
echo "🗄️  Running database migrations..."
docker-compose exec -T app php artisan migrate --force --quiet

if [ $? -eq 0 ]; then
    echo "✅ Database migrations completed"
else
    echo "⚠️  Migrations may have already been run"
fi

# Seed database if tables are empty
echo ""
echo "🌱 Checking if database needs seeding..."
USERS_COUNT=$(docker-compose exec -T mysql mysql -u laravel -plaravel_password laravel_pos -e "SELECT COUNT(*) FROM users;" 2>/dev/null | tail -1)

if [ "$USERS_COUNT" -eq 0 ] 2>/dev/null; then
    echo "   Running database seeder..."
    docker-compose exec -T app php artisan db:seed --quiet
    if [ $? -eq 0 ]; then
        echo "✅ Sample data seeded"
    fi
else
    echo "   Database already has data"
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" "$LARAVEL_PATH/.env"; then
    echo ""
    echo "🔑 Generating application key..."
    docker-compose exec -T app php artisan key:generate --quiet
fi

# Display summary
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 Docker containers started successfully!"
echo ""
echo "📍 Access your application:"
echo "   Web:       http://localhost:8000"
echo ""
echo "🔑 Default Accounts:"
echo "   Admin:     admin@example.com / admin123"
echo "   User:      john@example.com / password123"
echo ""
echo "📦 Running Services:"
docker-compose ps | tail -n +3 | awk '{print "   ✓ " $1 " (" $6 ")"}'
echo ""
echo "📋 Useful Commands:"
echo "   View logs:     docker-compose logs -f"
echo "   Stop:          docker-compose stop"
echo "   Restart:       docker-compose restart"
echo "   Shell:         docker-compose exec app bash"
echo "   Artisan:       docker-compose exec app php artisan [command]"
echo ""
echo "📖 For more info, see DOCKER_SETUP.md"
echo ""

