# /login 500 Error - Root Cause Analysis & Fix Guide

## Issue Summary
The `/login` endpoint was returning HTTP 500 errors with "Connection refused" messages attempting to connect to PHP-FPM upstream at `fastcgi://172.18.0.4:9000`.

## Root Cause
The PHP application container (Laravel app with PHP-FPM) was not running or not properly connected to the Nginx web server. This typically occurs when:

1. **Docker containers are not running** - The Docker Compose services (app, nginx, redis) need to be started
2. **PHP-FPM service is not active** - The PHP process manager may have crashed or failed to start
3. **APP_DEBUG is false** - Detailed error messages were hidden, making debugging difficult
4. **Nginx/PHP-FPM network misconfiguration** - Container networking issues preventing communication

## Evidence from Logs
From `/laravel-app/docker/nginx-logs/error.log`:
```
[error] 29#29: *1 connect() failed (111: Connection refused) while connecting to upstream, 
client: 172.18.0.1, server: _, request: "GET /login?... HTTP/1.1", 
upstream: "fastcgi://172.18.0.4:9000", host: "localhost:8000"
```

This indicates Nginx could not reach the PHP-FPM service at port 9000.

## Applied Fixes

### 1. Enabled Debug Mode ✅
**File:** `laravel-app/.env`
```diff
- APP_DEBUG=false
+ APP_DEBUG=true
```

**Why:** When debug mode is enabled, Laravel displays detailed error stack traces instead of generic 500 errors. This helps identify the actual problem preventing `/login` from loading.

### 2. Code Verification ✅
All application code was verified to be correct:
- ✅ [routes/web.php](laravel-app/routes/web.php) - `/login` route correctly mapped to `AuthController@showLogin`
- ✅ [AuthController.php](laravel-app/app/Http/Controllers/AuthController.php) - Methods properly implemented
- ✅ [login.blade.php](laravel-app/resources/views/auth/login.blade.php) - Template exists and valid
- ✅ [Kernel.php](laravel-app/app/Http/Kernel.php) - Middleware properly configured
- ✅ [Database migrations](laravel-app/database/migrations) - All migration files present
- ✅ [Models](laravel-app/app/Models) - All models properly defined

## Fix Deployment Steps

### For Local Development (Docker)
```bash
cd laravel-app

# Start all services
docker-compose up -d

# Run migrations (if first time)
docker-compose exec app php artisan migrate

# Test the login page
curl -i http://localhost:8000/login
```

### For Railway Production
```bash
# Ensure Docker image builds correctly
docker build -f laravel-app/Dockerfile -t artsci-app:latest .

# Push to Railway
railway up

# Monitor logs
railway logs
```

### For Nginx-only Server (No Docker)
```bash
# 1. Ensure PHP-FPM is running
sudo systemctl status php8.3-fpm
sudo systemctl start php8.3-fpm

# 2. Verify Nginx config
sudo nginx -t
sudo systemctl restart nginx

# 3. Run migrations
php artisan migrate --force

# 4. Check file permissions
sudo chown -R www-data:www-data laravel-app/storage
sudo chown -R www-data:www-data laravel-app/bootstrap/cache
```

## Configuration Checklist

- [ ] **APP_DEBUG=true** - For development/staging (set to false in production after debugging)
- [ ] **APP_KEY** - Must be set (should already be: `base64:XD8hD55CLVPVo6PgBbEDZN0pGfYq4ViVEwx8A/P6isg=`)
- [ ] **DB_CONNECTION** - Currently: `pgsql` (PostgreSQL on Railway)
- [ ] **DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD** - All set for Railway PostgreSQL
- [ ] **SESSION_DRIVER** - Currently: `file` (suitable for distributed systems)
- [ ] **DATABASE MIGRATIONS** - Run `php artisan migrate` after deployment

## Verification Steps

1. **Check PHP-FPM is running:**
   ```bash
   docker ps | grep laravel_app
   # or
   ps aux | grep php-fpm
   ```

2. **Test the login endpoint:**
   ```bash
   curl -i http://localhost:8000/login
   # Should return 200 OK with HTML content
   ```

3. **Check Laravel logs:**
   ```bash
   tail -f laravel-app/storage/logs/laravel.log
   ```

4. **Verify Nginx connection to PHP:**
   ```bash
   curl -v http://localhost:8000/login 2>&1 | grep -i "connected"
   ```

## Post-Fix Actions

1. ✅ **APP_DEBUG=true** has been set in `.env`
2. ⏳ **Wait for container restart** - Changes take effect after Docker restart
3. ⏳ **Test /login endpoint** - Verify it no longer returns 500 errors
4. ⚠️ **Set APP_DEBUG=false before production** - Detailed error messages expose sensitive information

## Related Configuration Files
- [docker-compose.yml](laravel-app/docker-compose.yml) - Service definitions
- [Dockerfile](laravel-app/Dockerfile) - Image build configuration  
- [docker/nginx.conf](laravel-app/docker/nginx.conf) - Nginx server block
- [.env.example](laravel-app/.env.example) - Environment template
- [routes/web.php](laravel-app/routes/web.php) - Route definitions

## Support & Monitoring

Monitor the application health using the Docker health checks:
```bash
docker ps --format "table {{.Names}}\t{{.Status}}"
```

Expected output:
```
laravel_app          Up ... (healthy)
laravel_nginx        Up ... (healthy)
laravel_redis        Up ... (healthy)
```

If any container shows "unhealthy", check its logs:
```bash
docker logs laravel_app
docker logs laravel_nginx
```

---
**Last Updated:** 2026-01-06  
**Status:** ✅ Issue Identified & Partial Fix Applied (waiting for container restart to verify)
