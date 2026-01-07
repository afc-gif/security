# ARTSCI POS System - Laravel Web Application

A modern Laravel-based Point of Sale (POS) system with an e-commerce shop, admin dashboard, and comprehensive product/order management.

## Features

### Customer Features
- Browse and purchase products
- Shopping cart functionality
- User authentication (Login/Register)
- Order history and tracking
- Responsive design

### Admin Features
- Dashboard with key metrics
- Product management (Create, Read, Update, Delete)
- Order management and status updates
- User management
- Sales analytics

## Project Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ShopController.php
│   │   │   └── AdminController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   └── OrderItem.php
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── shop/
│       │   ├── index.blade.php
│       │   ├── cart.blade.php
│       │   ├── orders.blade.php
│       │   └── order-details.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── products/
│       │   ├── orders/
│       │   └── users/
│       └── layout.blade.php
├── routes/
│   └── web.php
├── config/
├── composer.json
├── .env.example
└── README.md
```

## Setup Instructions

### Requirements
- PHP 8.1+
- Composer
- Laravel 10

### Installation

1. Navigate to the project directory:
```bash
cd /home/codecps/Desktop/security/laravel-app
```

2. Install dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run database migrations:
```bash
php artisan migrate
```

6. Create admin user (optional - add via seeders):
```bash
php artisan tinker
# Then run:
# App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin']);
```

7. Start the development server:
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Usage

### Customer Access
- Visit home page to browse products
- Create an account or login
- Add products to cart
- Proceed to checkout
- View order history

### Admin Access
- Navigate to `/admin/dashboard` (requires admin role)
- Manage products, orders, and users
- Update order statuses
- View sales metrics

## Database Schema

### Users Table
- id (Primary Key)
- name
- email (Unique)
- password
- role (user/admin)
- timestamps

### Products Table
- id (Primary Key)
- name
- description
- price (decimal)
- stock (integer)
- category
- image (optional)
- timestamps

### Orders Table
- id (Primary Key)
- user_id (Foreign Key)
- total_amount (decimal)
- status (pending/completed/cancelled)
- notes (optional)
- timestamps

### OrderItems Table
- id (Primary Key)
- order_id (Foreign Key)
- product_id (Foreign Key)
- quantity
- price (decimal at time of purchase)
- timestamps

## Routes

### Public Routes
- `GET /` - Home/Shop page
- `GET /products/{id}` - Product details
- `GET /login` - Login page
- `POST /login` - Handle login
- `GET /register` - Register page
- `POST /register` - Handle registration

### Authenticated Routes
- `POST /cart/add/{product}` - Add to cart
- `GET /cart` - View cart
- `DELETE /cart/{product}` - Remove from cart
- `POST /checkout` - Process checkout
- `GET /orders` - View user orders
- `GET /orders/{order}` - View order details

### Admin Routes (prefix: /admin)
- `GET /dashboard` - Admin dashboard
- `GET /products` - Products list
- `GET /products/create` - Create product form
- `POST /products` - Store product
- `GET /products/{product}/edit` - Edit product form
- `PUT /products/{product}` - Update product
- `DELETE /products/{product}` - Delete product
- `GET /orders` - Orders list
- `GET /orders/{order}` - Order details
- `PATCH /orders/{order}/status` - Update order status
- `GET /users` - Users list
- `DELETE /users/{user}` - Delete user

## Authentication

The application uses Laravel's built-in authentication with session-based login. Users can register and login with email and password.

### Default Admin Login
To create an admin user:

```php
php artisan tinker
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
]);
```

## Styling

The application uses a modern, professional design with:
- Blue primary color (#03A9F4)
- Yellow accent color (#FFEB3B)
- Responsive grid layout
- Smooth transitions and animations
- Mobile-friendly navigation

## Development Notes

- Uses SQLite database by default (can be changed in `.env`)
- Session-based authentication
- CSRF protection enabled
- Form validation on both client and server side
- Product stock management

## Future Enhancements

- Payment gateway integration (Stripe/PayPal)
- Email notifications
- Advanced reporting
- Inventory management
- Customer reviews and ratings
- Promotional codes and discounts
- Multi-language support

## Support

For issues or questions, please refer to [Laravel Documentation](https://laravel.com/docs)

## License

This project is open source and available under the MIT License.
