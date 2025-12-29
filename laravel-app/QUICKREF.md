# ARTSCI POS System - Quick Reference Card

## 🚀 Quick Start (TL;DR)

### Option 1: Using Docker (Recommended) 🐳
```bash
cd /home/codecps/Desktop/security/laravel-app
./docker-start.sh
```

Visit: **http://localhost:8000**

### Option 2: Using Nginx
```bash
cd /home/codecps/Desktop/security/laravel-app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# Start Nginx (see NGINX_SETUP.md for details):
nginx -c /home/codecps/Desktop/security/laravel-app/nginx.conf
```

Visit: **http://localhost:8000**

### Admin Login
- Email: `admin@example.com`
- Password: `admin123`

---

## 📍 Project Location

```
/home/codecps/Desktop/security/laravel-app/
```

---

## 🗺️ Main Routes

### Public
| Route | Page |
|-------|------|
| `/` | Shop/Home |
| `/login` | Login |
| `/register` | Registration |

### Authenticated User
| Route | Page |
|-------|------|
| `/cart` | Shopping Cart |
| `/orders` | My Orders |
| `/orders/{id}` | Order Details |

### Admin (/admin)
| Route | Page |
|-------|------|
| `/admin/dashboard` | Dashboard |
| `/admin/products` | Products |
| `/admin/orders` | Orders |
| `/admin/users` | Users |

---

## 💾 Database Models

### User
```
id, name, email, password, role (user/admin), timestamps
```

### Product
```
id, name, description, price, stock, category, image, timestamps
```

### Order
```
id, user_id, total_amount, status (pending/completed/cancelled), notes, timestamps
```

### OrderItem
```
id, order_id, product_id, quantity, price, timestamps
```

---

## 🔑 Key Commands

```bash
# Server
php artisan serve

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Cache
php artisan cache:clear

# Tinker (interactive shell)
php artisan tinker

# Make new model
php artisan make:model ModelName -m

# Make controller
php artisan make:controller ControllerName
```

---

## 👥 Default Accounts (After Seeding)

**Admin:**
- Email: `admin@example.com`
- Password: `admin123`

**Sample User:**
- Email: `john@example.com`
- Password: `password123`

---

## 🎨 Color Scheme

| Color | Hex | Usage |
|-------|-----|-------|
| Primary Blue | #03A9F4 | Buttons, Links |
| Accent Yellow | #FFEB3B | Admin, Highlights |
| Dark | #0A1428 | Text, Backgrounds |
| Light | #F0F4F9 | Backgrounds |

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `routes/web.php` | All routes |
| `app/Http/Controllers/` | Controllers |
| `app/Models/` | Database models |
| `resources/views/` | HTML templates |
| `public/css/app.css` | Styling |
| `.env` | Configuration |

---

## ✨ Features

✅ Product catalog  
✅ Shopping cart  
✅ Order management  
✅ User authentication  
✅ Admin dashboard  
✅ Product management  
✅ Order tracking  
✅ Responsive design  

---

## 🐛 Quick Fixes

**Port 8000 taken?**
```bash
php artisan serve --port 8001
```

**Clear everything?**
```bash
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Reset database?**
```bash
php artisan migrate:fresh --seed
```

**Can't login?**
```bash
php artisan tinker
User::where('email','admin@example.com')->update(['password'=>Hash::make('admin123')]);
exit
```

---

## 📚 Documentation

- **README.md** - Full docs
- **SETUP.md** - Setup guide
- **INSTALLATION.md** - Detailed install
- **PROJECT_SUMMARY.md** - Features overview

---

## 🎯 Next Steps

1. Run installation
2. Create some test products
3. Test purchasing as a customer
4. Test admin features
5. Customize as needed
6. Deploy when ready

---

**Happy Building!** 🚀
