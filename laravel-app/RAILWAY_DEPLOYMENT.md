# Railway Deployment Guide

## Pre-Deployment Checklist

✅ Code pushed to GitHub: https://github.com/afc-gif/security

## Environment Variables for Railway

When deploying on Railway, set these environment variables:

```
APP_NAME=ARTSCI
APP_ENV=production
APP_KEY=base64:XD8hD55CLVPVo6PgBbEDZN0pGfYq4ViVEwx8A/P6isg=
APP_DEBUG=false
APP_URL=https://your-railway-domain.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=your-railway-postgres-host
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-railway-password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=file

FILESYSTEM_DISK=public
```

## Deployment Steps

1. **Connect GitHub Repository**
   - Go to Railway.app
   - Click "New Project"
   - Select "Deploy from GitHub"
   - Select repository: `afc-gif/security`

2. **Add PostgreSQL Plugin**
   - In Railway project, click "Add Plugin"
   - Select "PostgreSQL"
   - Railway will automatically set DB credentials as env vars

3. **Configure Environment Variables**
   - Go to project settings
   - Add all env vars from the checklist above
   - Railway will merge with plugin vars automatically

4. **Add Build/Start Commands**
   In the Laravel app service settings:
   - Build Command: `composer install && php artisan migrate --force`
   - Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`

5. **Deploy**
   - Railway auto-deploys on GitHub push
   - Check deployment status in Railway dashboard

## Post-Deployment

After deployment:

1. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```
   (This runs automatically if configured)

2. **Admin Access**
   - Login: admin@example.com / admin123
   - Go to: /admin/dashboard
   - Navigate to "Solutions" to manage categories and items

3. **Solutions Management**
   - Create/edit solution categories
   - Add items with prices and images
   - All data stored in PostgreSQL

## Quick Links

- **App URL**: https://your-railway-domain.railway.app
- **Admin Login**: /login
- **Solutions Dashboard**: /admin/solutions
- **GitHub**: https://github.com/afc-gif/security

## Database Notes

- PostgreSQL credentials are in Railway environment
- All migrations run automatically on startup
- Images stored in `/storage/solutions/`
- Public disk configured for image serving

## Troubleshooting

If deployment fails:
1. Check Railway build logs
2. Verify all env vars are set correctly
3. Ensure PostgreSQL plugin is connected
4. Check app logs in Railway dashboard

For questions, refer to Railway documentation: https://docs.railway.app
