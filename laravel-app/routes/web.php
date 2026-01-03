<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SolutionController;

// Public routes
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
})->name('home');
Route::get('/solutions', [SolutionController::class, 'index'])->name('solutions.index');
Route::get('/solutions/{solution}', [SolutionController::class, 'show'])->name('solutions.show');
Route::get('/products/{product}', [ShopController::class, 'show'])->name('products.show');

// Authentication
Route::middleware('web')->group(function () {
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Shop routes (authenticated)
Route::middleware('auth')->group(function () {
    Route::post('/cart/add/{product}', [ShopController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
    Route::delete('/cart/{product}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [ShopController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [ShopController::class, 'orderDetails'])->name('orders.show');
});

// Admin routes (admin only)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Products
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products.index');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('admin.products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

    // Orders
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'orderDetails'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.update-status');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Categories & Menu Items
    Route::resource('categories', 'App\Http\Controllers\CategoryController');
    Route::resource('menu-items', 'App\Http\Controllers\MenuItemController');
    Route::post('/menu-items/{menuItem}/upload-image', [App\Http\Controllers\MenuItemController::class, 'uploadImage'])->name('menu-items.upload-image');

    // Solutions Management
    Route::resource('solutions', 'App\Http\Controllers\Admin\SolutionController');
    Route::resource('solutions.items', 'App\Http\Controllers\Admin\SolutionItemController', ['except' => ['index', 'show']]);
});

// API routes for live polling
Route::get('/api/categories', [App\Http\Controllers\CategoryController::class, 'apiIndex']);
Route::get('/api/categories/{category}/items', [App\Http\Controllers\CategoryController::class, 'apiItems']);
Route::get('/api/solutions', [SolutionController::class, 'apiIndex']);
