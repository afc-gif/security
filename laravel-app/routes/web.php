<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\MenuItemController as ApiMenuItemController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\UserAdminController as ApiUserAdminController;

// Authentication routes only
Route::middleware('web')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
    Route::get('/solutions', [ShopController::class, 'solutions'])->name('solutions.index');
    Route::post('/shop/{solutionItem}/add-to-cart', [ShopController::class, 'addToCart'])->name('shop.addToCart');
    Route::get('/cart', [ShopController::class, 'cart'])->name('cart.index');
    Route::delete('/cart/{productId}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [ShopController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [ShopController::class, 'orderDetails'])->name('orders.show');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});

Route::prefix('api')->group(function () {
    Route::get('/health', fn () => ['status' => 'ok']);
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::get('/menu-items', [ApiMenuItemController::class, 'index']);
    Route::get('/menu-items/lookup', [ApiMenuItemController::class, 'lookup']);
});

Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::post('/categories', [ApiCategoryController::class, 'store']);
    Route::put('/categories/{category}', [ApiCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [ApiCategoryController::class, 'destroy']);

    Route::post('/menu-items', [ApiMenuItemController::class, 'store']);
    Route::put('/menu-items/{menuItem}', [ApiMenuItemController::class, 'update']);
    Route::post('/menu-items/{menuItem}/toggle-sold-out', [ApiMenuItemController::class, 'toggleSoldOut']);
    Route::post('/menu-items/{menuItem}/regenerate-barcode', [ApiMenuItemController::class, 'regenerateBarcode']);
    Route::delete('/menu-items/{menuItem}', [ApiMenuItemController::class, 'destroy']);

    Route::get('/orders/summary', [ApiOrderController::class, 'summary']);
    Route::get('/orders/export', [ApiOrderController::class, 'export']);
    Route::post('/orders/purge', [ApiOrderController::class, 'purge']);

    Route::get('/users', [ApiUserAdminController::class, 'index']);
    Route::put('/users/{user}', [ApiUserAdminController::class, 'update']);
    Route::delete('/users/{user}', [ApiUserAdminController::class, 'destroy']);
});

Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/orders', [ApiOrderController::class, 'index']);
    Route::get('/orders/{order}', [ApiOrderController::class, 'show']);
    Route::post('/orders', [ApiOrderController::class, 'store']);
    Route::post('/orders/{order}/send-to-kitchen', [ApiOrderController::class, 'sendToKitchen']);
    Route::post('/orders/{order}/kitchen-status', [ApiOrderController::class, 'updateKitchenStatus']);
    Route::post('/orders/{order}/approve', [ApiOrderController::class, 'approve']);
});

// Admin routes (admin only) - Dashboard, Products & Barcodes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Products & Barcodes
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products.index');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('admin.products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

    // Users Management (view pending, approve, assign roles)
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
    Route::patch('/users/{user}/approve/{role}', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::patch('/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Solutions/Products Management
    Route::resource('solutions', 'App\Http\Controllers\Admin\SolutionController', ['names' => [
        'index' => 'admin.solutions.index',
        'show' => 'admin.solutions.show',
        'create' => 'admin.solutions.create',
        'store' => 'admin.solutions.store',
        'edit' => 'admin.solutions.edit',
        'update' => 'admin.solutions.update',
        'destroy' => 'admin.solutions.destroy',
    ]]);
    Route::resource('solutions.items', 'App\Http\Controllers\Admin\SolutionItemController', [
        'except' => ['index', 'show'],
        'names' => [
            'create' => 'admin.solutions.items.create',
            'store' => 'admin.solutions.items.store',
            'edit' => 'admin.solutions.items.edit',
            'update' => 'admin.solutions.items.update',
            'destroy' => 'admin.solutions.items.destroy',
        ]
    ]);
});

// POS API routes (for barcode scanning and product lookup)
// GET /products is public (for frontend product display), others require auth
Route::prefix('api/pos')->group(function () {
    Route::get('/products', [App\Http\Controllers\PosController::class, 'getProducts']); // Public product listing
});

Route::middleware('auth')->prefix('api/pos')->group(function () {
    Route::get('/barcode/{barcode}', [App\Http\Controllers\PosController::class, 'lookupBarcode']);
    Route::get('/search/{query}', [App\Http\Controllers\PosController::class, 'searchProducts']);
    Route::post('/complete-sale', [App\Http\Controllers\PosController::class, 'completeSale']);
});

// Barcode routes (accessible to authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/barcode/{solutionItem}/view', [BarcodeController::class, 'view'])->name('barcode.view');
    Route::get('/barcode/{solutionItem}/download', [BarcodeController::class, 'download'])->name('barcode.download');
    Route::get('/barcode/{solutionItem}/download-image', [BarcodeController::class, 'downloadImage'])->name('barcode.download-image');
    Route::get('/barcode/{solutionItem}/svg', [BarcodeController::class, 'svg'])->name('barcode.svg');
    Route::get('/barcode/{solutionItem}/print', [BarcodeController::class, 'printLabel'])->name('barcode.print');
    Route::get('/barcode/{solutionItem}/info', [BarcodeController::class, 'metadata'])->name('barcode.metadata');
});

// POS System (accessible to authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/pos', function () {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'pos'], true)) {
            abort(403, 'Unauthorized');
        }
        return view('pos.index');
    })->name('pos.index');
    
    // Receipt view route
    Route::get('/pos/receipt/{order}', function (App\Models\Order $order) {
        // Ensure user can only view their own sales or admins can see all
        $user = auth()->user();
        if ($user && $user->role === 'pos' && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        return view('pos.receipt', compact('order'));
    })->name('pos.receipt');
});
