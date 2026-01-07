# Docker & Nginx Container Setup

Your Laravel POS system now supports both Docker containerization and Nginx web server.

## Architecture Overview

```
┌─────────────────────────────────────┐
│        Your Browser                  │
│     http://localhost:8000            │
└──────────────┬──────────────────────┘
               │
    ┌──────────▼──────────────┐
    │   Nginx Web Server      │
    │   (Port 8000)           │
    │   ┌──────────────────┐  │
    │   │   docker/        │  │
    │   │   nginx.conf     │  │
    │   └──────────────────┘  │
    └──────────┬──────────────┘
               │
    ┌──────────▼──────────────────┐
    │  PHP-FPM Application         │
    │  (Port 9000)                │
    │  ┌──────────────────────┐   │
    │  │  Laravel App         │   │
    │  │  (Models, Routes,    │   │
    │  │   Controllers, etc)  │   │
    │  └──────────────────────┘   │
    └──────────┬──────────────────┘
               │
    ┌──────────▴──────────────┐
    │                         │
┌───▼─────────┐      ┌───────▼────────┐
│   Postgres     │      │     Redis      │
│   Database  │      │     Cache      │
│   Port 5432 │      │   Port 6379    │
└─────────────┘      └────────────────┘
```

## Docker Setup

### Files Created for Docker

1. **Dockerfile** - Defines PHP-FPM image with all Laravel dependencies
2. **docker-compose.yml** - Orchestrates 4 services: PHP, Nginx, Postgres, Redis
3. **docker/nginx.conf** - Nginx configuration for Docker environment
4. **.dockerignore** - Files excluded from Docker build
5. **DOCKER_SETUP.md** - Complete Docker guide with troubleshooting
6. **docker-start.sh** - Automated startup script (executable)
7. **docker-stop.sh** - Automated shutdown script (executable)

### Quick Start with Docker

```bash
cd /home/codecps/Desktop/security/laravel-app
./docker-start.sh
```

This script:
- ✅ Builds Docker images
- ✅ Starts all containers (PHP, Nginx, Postgres, Redis)
- ✅ Runs database migrations
- ✅ Seeds sample data
- ✅ Displays service status

Visit: **http://localhost:8000**

## Nginx Setup (Non-Docker)

### Files Created for Nginx

1. **nginx.conf** - Standalone Nginx configuration for port 8000
2. **NGINX_SETUP.md** - Comprehensive Nginx setup guide
3. **NGINX_MIGRATION.md** - Migration summary
4. **start-nginx.sh** - Automated startup script (executable)
5. **stop-nginx.sh** - Automated shutdown script (executable)

### Quick Start with Nginx

```bash
cd /home/codecps/Desktop/security/laravel-app
./start-nginx.sh
```

Or manually:
```bash
nginx -c /home/codecps/Desktop/security/laravel-app/nginx.conf
```

## Comparison

| Feature | Docker | Nginx |
|---------|--------|-------|
| **Setup Time** | 5-10 min | 3-5 min |
| **Database** | Containerized Postgres | Requires local Postgres |
| **Environment** | Isolated containers | Local system |
| **Portability** | Run anywhere | System-dependent |
| **Production-Ready** | Yes | Requires additional setup |
| **Memory Usage** | 500MB+ | 100MB+ |
| **Ease of Use** | One command | Multiple dependencies |
| **Recommended For** | Production/Teams | Local development |

## Docker Services Included

### 1. Laravel App (PHP-FPM)
- Image: Custom Dockerfile (Alpine 8.3-fpm)
- Port: 9000 (internal)
- Volume: Full project sync
- Features: Auto-restart, health checks

### 2. Nginx Web Server
- Image: nginx:alpine
- Port: 8000 (accessible)
- Config: docker/nginx.conf
- Features: Gzip, caching, security headers

### 3. Postgres Database
- Image: postgres:15-alpine
- Port: 5432
- Database: laravel_pos
- User: laravel / password: laravel_password
- Data: docker/postgres-data/ (persistent)

### 4. Redis Cache
- Image: redis:alpine
- Port: 6379
- Data: docker/redis-data/ (persistent)

## Docker Commands

### Start Services
```bash
docker-compose up -d           # Background
docker-compose up              # Foreground (shows logs)
./docker-start.sh              # Using helper script
```

### Stop Services
```bash
docker-compose stop            # Graceful stop
docker-compose kill            # Force stop
./docker-stop.sh               # Using helper script
```

### View Services
```bash
docker-compose ps              # List running containers
docker-compose logs -f         # Follow all logs
docker-compose logs app        # View app logs only
```

### Execute Commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan tinker
docker-compose exec postgres psql -U laravel -d laravel_pos
docker-compose exec app bash   # Access container shell
```

### Cleanup
```bash
docker-compose down            # Remove containers
docker-compose down -v         # Remove containers + volumes
docker system prune            # Clean up unused resources
```

## Nginx Commands

### Start Services
```bash
./start-nginx.sh               # Using helper script
nginx -c /path/to/nginx.conf   # Manual start
```

### Stop Services
```bash
./stop-nginx.sh                # Using helper script
nginx -s stop                  # Manual stop
```

### View Logs
```bash
tail -f /var/log/nginx/laravel_access.log
tail -f /var/log/nginx/laravel_error.log
```

## Environment Comparison

### Docker Environment
```
Web Traffic → Nginx Container → PHP-FPM Container → Postgres Container
All isolated, consistent, production-like
```

### Nginx Environment
```
Web Traffic → Nginx (local) → PHP-FPM (local) → Postgres (local/required)
Direct on system, minimal overhead
```

## When to Use Each

### Use Docker When:
✅ Working in a team (consistent environments)
✅ Planning to deploy to production
✅ Want to avoid system dependencies
✅ Need database included
✅ Using CI/CD pipelines
✅ Running on different OS (macOS, Windows, Linux)

### Use Nginx When:
✅ Quick local development
✅ Minimal resource usage needed
✅ Already have local Postgres/PHP
✅ Single developer setup
✅ Testing Nginx configuration

## First-Time Setup

### If you've never run the app:

**Option 1: Docker (Recommended)**
```bash
cd /home/codecps/Desktop/security/laravel-app
./docker-start.sh
# Wait for "All services started successfully!" message
# Visit http://localhost:8000
```

**Option 2: Nginx**
```bash
cd /home/codecps/Desktop/security/laravel-app
./start-nginx.sh
# Wait for "All services started successfully!" message
# Visit http://localhost:8000
```

## Troubleshooting

### Docker Issues
- See **DOCKER_SETUP.md** for comprehensive troubleshooting

### Nginx Issues
- See **NGINX_SETUP.md** for comprehensive troubleshooting

### Both Not Working
```bash
# Check what's using port 8000
lsof -i :8000              # macOS/Linux
netstat -ano | find ":8000" # Windows

# Kill process using port 8000
kill -9 <PID>              # macOS/Linux
taskkill /PID <PID> /F     # Windows
```

## Default Accounts

After setup, login with:
- **Admin:** admin@example.com / admin123
- **User:** john@example.com / password123

## Documentation Files

📖 **Setup Guides:**
- DOCKER_SETUP.md - Complete Docker guide
- NGINX_SETUP.md - Complete Nginx guide
- SETUP.md - General setup

📋 **References:**
- QUICKREF.md - Quick reference
- START_HERE.txt - Quick start
- README.md - Full documentation

## Next Steps

1. Choose Docker or Nginx
2. Run the startup script or command
3. Wait for "All services started successfully!"
4. Visit http://localhost:8000
5. Login with provided credentials
6. Start using the POS system!

---

**Both Docker and Nginx are fully configured and ready to use!**
Choose whichever best fits your needs.
