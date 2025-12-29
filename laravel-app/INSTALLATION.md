# INSTALLATION & TESTING GUIDE

## Complete Step-by-Step Installation

### Prerequisites
- PHP 8.1 or higher
- Composer installed
- Git (optional)

### Step 1: Navigate to Project

```bash
cd /home/codecps/Desktop/security/laravel-app
```

### Step 2: Install Composer Dependencies

```bash
composer install
```

This installs all required PHP packages. Takes 2-5 minutes.

### Step 3: Setup Environment File

```bash
cp .env.example .env
```

This creates your environment configuration file.

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

This sets `APP_KEY` in your `.env` file for encryption.

### Step 5: Create Database

The application uses SQLite by default (no additional setup needed).

If database file doesn't exist, it will be created automatically. To manually create:

```bash
php artisan migrate
```

### Step 6: Create Sample Data (Optional)

```bash
# Create admin and sample users + products
php artisan db:seed
```

Or manually create an admin user:

```bash
php artisan tinker
```

Then paste this and press Enter:
```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);
```

Press `exit` to leave tinker.

### Step 7: Start Development Server

```bash
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000]
```

### Step 8: Access the Application

Open your browser and go to: **http://localhost:8000**

---

## Testing the Application

### Test 1: Homepage & Products
1. Visit http://localhost:8000
2. You should see the ARTSCI shop
3. Browse products

### Test 2: User Registration
1. Click "Register" or go to http://localhost:8000/register
2. Fill in name, email, password
3. Click "Register"
4. You should be logged in automatically

### Test 3: Shopping Cart
1. Click on a product or "Add to Cart"
2. Quantity should be set to 1
3. Click "Add to Cart"
4. Click cart icon in navigation
5. Product should appear in cart
6. Try removing the product

### Test 4: Checkout
1. Add products to cart
2. Click cart → "Proceed to Checkout"
3. Enter optional notes
4. Click "Proceed to Checkout"
5. You should see order confirmation
6. Order appears in "My Orders"

### Test 5: View Orders
1. Click "My Orders" in navigation
2. See list of your orders with details
3. Click "View Details" to see items

### Test 6: Admin Dashboard
1. Logout first
2. Login with admin credentials:
   - Email: `admin@example.com`
   - Password: `admin123`
3. Click "Admin" in navigation
4. You should see admin dashboard

### Test 7: Product Management
1. In admin area, click "Products"
2. Click "Add Product"
3. Fill in product details:
   - Name: "Test Product"
   - Price: 99.99
   - Stock: 10
   - Category: "Test"
4. Click "Add Product"
5. Product should appear in list

### Test 8: Edit Product
1. In products list, click "Edit"
2. Change any field
3. Click "Update Product"
4. Changes should be saved

### Test 9: Delete Product
1. In products list, click "Delete"
2. Confirm the action
3. Product should be removed

### Test 10: Order Management
1. Click "Orders" in admin navigation
2. See all orders from all customers
3. Click "View" to see details
4. Change status and click "Update Status"

### Test 11: User Management
1. Click "Users" in admin navigation
2. See all users with roles
3. Try deleting a non-admin user (except yourself)

---

## Troubleshooting

### Error: "composer not found"
Install Composer: https://getcomposer.org/download/

### Error: "Port 8000 already in use"
Use a different port:
```bash
php artisan serve --port 8001
```

### Error: "database.sqlite not found"
Create it:
```bash
touch database/database.sqlite
php artisan migrate
```

### Error: "Class not found"
Clear autoload cache:
```bash
composer dump-autoload
```

### Cannot login to admin account
Reset it in tinker:
```bash
php artisan tinker
User::where('email', 'admin@example.com')->update(['password' => Hash::make('admin123')]);
exit
```

### Forgot email/password
View users:
```bash
php artisan tinker
User::all();
exit
```

### Want to reset everything
```bash
php artisan migrate:fresh --seed
```

Warning: This deletes all data and recreates sample data.

---

## Common Tasks

### Add a Test Product Manually
```bash
php artisan tinker
```

```php
App\Models\Product::create([
    'name' => 'My Product',
    'description' => 'Product description',
    'price' => 99.99,
    'stock' => 5,
    'category' => 'My Category'
]);
exit
```

### View All Users
```bash
php artisan tinker
App\Models\User::all();
exit
```

### View All Products
```bash
php artisan tinker
App\Models\Product::all();
exit
```

### View All Orders
```bash
php artisan tinker
App\Models\Order::with('user')->get();
exit
```

### Change User Role to Admin
```bash
php artisan tinker
$user = App\Models\User::find(2); // ID 2
$user->role = 'admin';
$user->save();
exit
```

### Delete All Orders
```bash
php artisan tinker
App\Models\Order::truncate();
exit
```

---

## Database Files

SQLite database location:
```
/home/codecps/Desktop/security/laravel-app/database/database.sqlite
```

To backup database:
```bash
cp database/database.sqlite database/database.sqlite.backup
```

To restore backup:
```bash
cp database/database.sqlite.backup database/database.sqlite
```

---

## Environment Configuration

Edit `.env` to change:

```env
# App name
APP_NAME="ARTSCI POS"

# Debug mode (false in production)
APP_DEBUG=true

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Or use MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=artsci
# DB_USERNAME=root
# DB_PASSWORD=yourpassword
```

---

## Verification Checklist

After installation, verify:

- [x] php artisan serve works
- [x] http://localhost:8000 loads
- [x] Products display
- [x] Can register new user
- [x] Can login
- [x] Can add to cart
- [x] Can checkout
- [x] Can view orders
- [x] Can login as admin
- [x] Admin dashboard displays
- [x] Can manage products
- [x] Can manage orders
- [x] Can manage users

---

## Performance Tips

1. **Cache routes** (production):
   ```bash
   php artisan route:cache
   ```

2. **Optimize autoloader**:
   ```bash
   composer install --optimize-autoloader
   ```

3. **Cache configuration**:
   ```bash
   php artisan config:cache
   ```

4. **Clear cache**:
   ```bash
   php artisan cache:clear
   ```

---

## File Permissions (Linux/Mac)

If you get permission errors:

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage logs
```

---

## Next Steps

1. **Customize design**: Edit `public/css/app.css`
2. **Add products**: Use admin dashboard or tinker
3. **Configure email**: Update `MAIL_*` in `.env`
4. **Deploy to server**: Follow deployment guide in README.md
5. **Add features**: Extend controllers and models

---

## Support Files

- **README.md** - Full documentation
- **SETUP.md** - Quick setup guide
- **PROJECT_SUMMARY.md** - Feature overview
- **This file** - Installation & testing guide

---

**You're all set! Start building your POS system.** 🚀
