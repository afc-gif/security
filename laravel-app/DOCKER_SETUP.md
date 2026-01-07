# Docker Setup Guide for ARTSCI POS System

This guide covers setting up and running your Laravel POS application using Docker containers.

## What is Docker?

Docker is containerization technology that packages your entire application (Laravel, Nginx, PHP, Postgres, Redis) into isolated containers. This ensures consistent environments across development, testing, and production.

## Prerequisites

- **Docker** - [Install Docker Desktop](https://www.docker.com/products/docker-desktop)
- **Docker Compose** - Usually included with Docker Desktop
- At least 2GB RAM available for containers

## Quick Start

### 1. Build and Start Containers

```bash
cd /home/codecps/Desktop/security/laravel-app

# Build Docker images and start all services
docker-compose up -d
```

**What this does:**
- Builds a PHP-FPM image with Laravel
- Starts Nginx web server (port 8000)
- Starts Postgres database (port 5432)
- Starts Redis cache server (port 6379)

### 2. Initialize Database

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed sample data
docker-compose exec app php artisan db:seed
```

### 3. Access Your Application

Visit: **http://localhost:8000**

**Default Accounts:**
- Admin: admin@example.com / admin123
- User: john@example.com / password123

## Docker Services

### 1. Laravel App (PHP-FPM)
- **Container:** laravel_app
- **Port:** 9000 (internal only)
- **Image:** Custom Dockerfile
- **Purpose:** Runs your Laravel application

### 2. Nginx Web Server
- **Container:** laravel_nginx
- **Port:** 8000 (accessible)
- **Image:** nginx:alpine
- **Purpose:** Serves requests to the Laravel app

### 3. Postgres Database
- **Container:** laravel_postgres
- **Port:** 5432 (accessible)
- **Image:** postgres:15-alpine
- **Credentials:**
  - Database: laravel_pos
  - User: laravel
  - Password: laravel_password
  - Root Password: root_password

### 4. Redis Cache
- **Container:** laravel_redis
- **Port:** 6379 (accessible)
- **Image:** redis:alpine
- **Purpose:** Session and cache storage

## Common Docker Commands

### View running containers

```bash
docker-compose ps
```

### View container logs

```bash
# All containers
docker-compose logs

# Specific container
docker-compose logs app
docker-compose logs nginx
docker-compose logs postgres

# Follow logs in real-time
docker-compose logs -f app
```

### Execute commands inside containers

```bash
# Run artisan commands
docker-compose exec app php artisan tinker
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Access Postgres CLI
docker-compose exec postgres psql -U laravel -d laravel_pos

# Access Laravel shell
docker-compose exec app bash
```

### Stop containers

```bash
# Graceful stop
docker-compose stop

# Force stop
docker-compose kill
```

### Start containers (after stopping)

```bash
docker-compose start
```

### Remove containers and volumes

```bash
# Remove containers but keep volumes
docker-compose down

# Remove everything including volumes
docker-compose down -v
```

### Rebuild after code changes

```bash
docker-compose build
docker-compose up -d
```

## File Structure

```
laravel-app/
├── Dockerfile              # PHP-FPM image definition
├── docker-compose.yml      # Service orchestration
├── .dockerignore           # Files to exclude from Docker
├── docker/
│   ├── nginx.conf          # Nginx configuration
│   ├── postgres-data/         # Postgres database files
│   ├── redis-data/         # Redis data files
│   └── nginx-logs/         # Nginx logs
├── app/                    # Laravel application code
├── public/                 # Web-accessible files
├── storage/                # Laravel storage
└── bootstrap/              # Laravel bootstrap
```

## Environment Configuration

The Docker services use these environment variables (in docker-compose.yml):

```yaml
Database:
  DB_HOST=postgres
  DB_DATABASE=laravel_pos
  DB_USERNAME=laravel
  DB_PASSWORD=laravel_password

Cache:
  REDIS_HOST=redis
  REDIS_PORT=6379
```

To change these, edit `docker-compose.yml` before running `docker-compose up`.

## Troubleshooting

### Issue: "Cannot connect to Docker daemon"

**Solution:** Ensure Docker Desktop is running
```bash
docker ps  # Test if Docker is running
```

### Issue: "Port 8000 already in use"

**Solution:** Change port in docker-compose.yml:
```yaml
ports:
  - "8001:80"  # Change 8000 to 8001 (or another port)
```

Then restart: `docker-compose restart nginx`

### Issue: "Postgres connection refused"

**Solution:** Postgres container may still be starting. Wait and try again:
```bash
docker-compose logs postgres
docker-compose restart postgres
```

### Issue: "Permission denied in storage directory"

**Solution:** Set proper permissions:
```bash
docker-compose exec app chmod -R 755 storage
docker-compose exec app chmod -R 777 bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap
```

### Issue: "Composer install fails during build"

**Solution:** Increase Docker memory allocation to at least 2GB

### Issue: "Database already exists" during migration

**Solution:** Clear and reset:
```bash
docker-compose down -v  # Remove volumes
docker-compose up -d    # Start fresh
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### Issue: "502 Bad Gateway" error

**Solution:** Check PHP-FPM health:
```bash
docker-compose ps
docker-compose logs app
docker-compose exec app php-fpm -v
```

## Database Access

### Via Docker Container

```bash
docker-compose exec postgres psql -U laravel -d laravel_pos
```

### Via Local Postgres Client

```bash
psql -h 127.0.0.1 -U laravel -d laravel_pos
```

Enter password: `laravel_password`

### Backup Database

```bash
docker-compose exec postgres pg_dump -U laravel -d laravel_pos > backup.sql
```

### Restore Database

```bash
docker-compose exec -T postgres psql -U laravel -d laravel_pos < backup.sql
```

## Advanced Configuration

### Custom PHP Configuration

Create `docker/php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 60
```

Add to Dockerfile:
```dockerfile
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
```

### Production Setup

For production deployment, consider:

1. **Use managed database** instead of container
2. **Use managed cache** (AWS ElastiCache, etc.)
3. **Implement auto-scaling**
4. **Use container registry** (Docker Hub, ECR, etc.)
5. **Implement health checks** (already configured)
6. **Use reverse proxy** (AWS ALB, etc.)

Example production docker-compose.yml:
```yaml
version: '3.8'
services:
  app:
    image: your-registry/laravel-app:latest
    # Remove build, use pre-built image
    environment:
      DB_HOST: prod-database.example.com
      REDIS_HOST: prod-redis.example.com
    # Configure auto-restart
    restart: always
```

### Docker Swarm Deployment

For multiple servers:
```bash
docker swarm init
docker stack deploy -c docker-compose.yml laravel
```

### Kubernetes (Advanced)

For enterprise deployments, convert to Kubernetes manifests using:
```bash
kompose convert -f docker-compose.yml
```

## Performance Optimization

### 1. Enable BuildKit for faster builds

```bash
DOCKER_BUILDKIT=1 docker-compose build
```

### 2. Use Alpine images for smaller sizes

Already configured in docker-compose.yml

### 3. Multi-stage builds

Implemented in Dockerfile to reduce final image size

### 4. Resource limits

Add to docker-compose.yml services:
```yaml
deploy:
  resources:
    limits:
      cpus: '0.5'
      memory: 512M
```

## Monitoring and Logging

### View all logs

```bash
docker-compose logs --tail=100 -f
```

### Monitor resource usage

```bash
docker stats
```

### View specific service logs with timestamps

```bash
docker-compose logs --timestamps -f app
```

## Cleaning Up

### Remove unused images

```bash
docker image prune
```

### Remove stopped containers

```bash
docker container prune
```

### Full cleanup (use with caution!)

```bash
docker system prune -a
```

## Next Steps

1. ✅ Install Docker and Docker Compose
2. ✅ Run `docker-compose up -d`
3. ✅ Run migrations and seeding
4. ✅ Visit http://localhost:8000
5. ✅ Refer to this guide for troubleshooting

## Documentation

- **DOCKER_SETUP.md** - This guide
- **NGINX_SETUP.md** - Nginx configuration details
- **SETUP.md** - General setup
- **QUICKREF.md** - Quick reference

## Support Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Guide](https://laravel.com/docs/deployment#docker)
- [Best Practices for Building Docker Images](https://docs.docker.com/develop/dev-best-practices/)

---

**Docker setup complete!** Your entire application stack is now containerized and ready to deploy anywhere Docker is supported.
