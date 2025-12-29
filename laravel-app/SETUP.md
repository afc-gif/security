# ARTSCI Laravel POS System - Quick Start Guide

## Overview

Your website has been successfully converted into a fully-functional Laravel web application with:
- ✅ E-commerce shop with product catalog
- ✅ Shopping cart system
- ✅ User authentication (Login/Register)
- ✅ Order management system
- ✅ Admin dashboard
- ✅ Product management
- ✅ Complete POS functionality

## Project Location

```
/home/codecps/Desktop/security/laravel-app/
```

## Quick Setup (5 minutes)

### Step 1: Install Dependencies
```bash
cd /home/codecps/Desktop/security/laravel-app
composer install
```

### Step 2: Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Create Database
```bash
# SQLite (default - no setup needed)
# Or for MySQL, update .env first, then:
php artisan migrate
```

### Step 4: Create Admin User
```bash
php artisan tinker
```

Then run:
```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);
```

Exit tinker with: `exit`

### Step 5: Start the Server with Nginx

**Option 1: Using Nginx (Recommended)**
```bash
nginx -c /home/codecps/Desktop/security/laravel-app/nginx.conf
```

For detailed setup, see [NGINX_SETUP.md](NGINX_SETUP.md)

**Option 2: Using PHP Built-in Server (Development Only)**
```bash
php artisan serve
```

Visit: **http://localhost:8000**

## Default Credentials

**Admin Account:**
- Email: `admin@example.com`
- Password: `admin123`

## Features Breakdown

### 🛍️ Customer Shop
- Browse all products
- Add items to cart
- Checkout process
- View order history
- Track orders

**Access:** Home page `/`

### 👥 User Authentication
- User registration
- Login/Logout
- Password hashing
- Session management

**Access:** `/login`, `/register`

### 📊 Admin Dashboard
- View sales metrics
- Total products, orders, revenue, users
- Recent orders overview
- Quick access to management panels

**Access:** `/admin/dashboard` (admin only)

### 📦 Product Management
- Create new products
- Edit product details
- Update stock levels
- Delete products
- Upload product images

**Access:** `/admin/products`

### 🛒 Order Management
- View all orders
- See order details
- Update order status (pending/completed/cancelled)
- Track customer information

**Access:** `/admin/orders`

### 👤 User Management
- View all users
- Delete users
- User role management

**Access:** `/admin/users`

## Database Structure

### Users Table
```
id, name, email, password, role (user/admin), created_at, updated_at
```

### Products Table
```
id, name, description, price, stock, category, image, created_at, updated_at
```

### Orders Table
```
id, user_id, total_amount, status, notes, created_at, updated_at
```

### OrderItems Table
```
id, order_id, product_id, quantity, price, created_at, updated_at
```

## Important Files

| File | Purpose |
|------|---------|
| `routes/web.php` | All application routes |
| `app/Http/Controllers/` | Business logic |
| `app/Models/` | Database models |
| `resources/views/` | HTML templates |
| `public/css/app.css` | Styling |
| `public/js/app.js` | JavaScript |
| `.env` | Environment configuration |
| `composer.json` | PHP dependencies |

## Common Commands

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear

# Make a new controller
php artisan make:controller ControllerName

# Make a new model
php artisan make:model ModelName

# Tinker (interactive shell)
php artisan tinker
```

## Styling & Design

The application uses a modern design system:

**Color Scheme:**
- Primary Blue: `#03A9F4`
- Accent Yellow: `#FFEB3B`
- Dark: `#0A1428`
- Light Background: `#F0F4F9`

**CSS Location:** `public/css/app.css`

All Blade templates are fully styled and responsive.

## Navigation Structure

```
Home (/)
├── Shop (/)
├── Products
├── Authentication
│   ├── Login (/login)
│   └── Register (/register)
├── User Panel (auth required)
│   ├── Cart (/cart)
│   ├── Checkout (/checkout)
│   └── Orders (/orders)
└── Admin Panel (/admin/dashboard - admin only)
    ├── Dashboard
    ├── Products
    ├── Orders
    └── Users
```

## Testing the System

### As a Customer:
1. Go to http://localhost:8000
2. Click "Register"
3. Create an account
4. Browse products
5. Add to cart
6. Checkout
7. View orders

### As an Admin:
1. Login with admin credentials
2. Click "Admin" in navbar
3. Manage products, orders, users

## Troubleshooting

### Port 8000 already in use?
```bash
php artisan serve --port 8001
```

### Database not found?
```bash
# Check your .env file:
# DB_CONNECTION=sqlite
# DB_DATABASE=/path/to/database.sqlite

php artisan migrate
```

### Migrations not working?
```bash
php artisan migrate:fresh  # WARNING: Resets database
```

### Composer issues?
```bash
composer dump-autoload
composer install --no-dev
```

## Security Notes

⚠️ **Before Production:**

1. Update `.env` with strong credentials
2. Set `APP_DEBUG=false` in production
3. Use environment variables for sensitive data
4. Enable HTTPS
5. Set proper file permissions
6. Keep Laravel updated

## Next Steps

### To Extend the Application:

1. **Add Payment Gateway:**
   - Integrate Stripe or PayPal
   - Update checkout controller

2. **Email Notifications:**
   - Send order confirmations
   - Add mailable classes

3. **Product Reviews:**
   - Create review model
   - Add rating system

4. **Inventory Alerts:**
   - Low stock notifications
   - Automatic restocking

5. **Advanced Analytics:**
   - Sales reports
   - Customer analytics
   - Dashboard charts

## Support Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Blade Templating](https://laravel.com/docs/10.x/blade)
- [Eloquent ORM](https://laravel.com/docs/10.x/eloquent)
- [Authentication](https://laravel.com/docs/10.x/authentication)

## File Structure Summary

```
laravel-app/
├── app/                      # Application code
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/     # Route controllers
│   │   ├── Kernel.php
│   │   └── Middleware/      # HTTP middleware
│   ├── Models/              # Database models
│   └── Providers/           # Service providers
├── bootstrap/               # Framework bootstrap files
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/
├── public/                  # Web root
│   ├── css/app.css
│   ├── js/app.js
│   └── index.php
├── resources/
│   └── views/              # Blade templates
│       ├── auth/
│       ├── admin/
│       ├── shop/
│       └── layout.blade.php
├── routes/                  # Application routes
│   └── web.php
├── storage/                 # Logs, cache, uploads
├── vendor/                  # Composer dependencies
├── .env                     # Environment variables
├── .env.example
├── artisan                  # Artisan CLI
├── composer.json
└── README.md
```

## That's It! 🎉

Your Laravel POS system is ready to use. Start the server and begin selling!

For questions or customizations, refer to the Laravel documentation or modify the code as needed.
