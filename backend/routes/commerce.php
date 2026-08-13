<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Commerce\ShopController;
use App\Http\Controllers\Operations\AdminController;
use App\Http\Controllers\Commerce\BarcodeController;
use App\Http\Controllers\Commerce\ApiCategoryController;
use App\Http\Controllers\Commerce\ApiMenuItemController;
use App\Http\Controllers\Commerce\ApiOrderController;
use App\Models\User;

// Public E-Commerce Shop
Route::middleware('web')->group(function () {
    Route::get('/solutions', [ShopController::class, 'solutions'])->name('solutions.index');
    Route::post('/shop/{solutionItem}/add-to-cart', [ShopController::class, 'addToCart'])->name('shop.addToCart');
    Route::get('/cart', [ShopController::class, 'cart'])->name('cart.index');
    Route::delete('/cart/{productId}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [ShopController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [ShopController::class, 'orderDetails'])->name('orders.show');
});

// Public API for Solutions & Catalog
Route::prefix('api')->group(function () {
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::get('/menu-items', [ApiMenuItemController::class, 'index']);
    Route::get('/menu-items/lookup', [ApiMenuItemController::class, 'lookup']);
    Route::get('/menu-items/search', [ApiMenuItemController::class, 'search']);
    Route::get('/solutions', [ShopController::class, 'solutionsApi']);
});

// Admin Commerce API
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::post('/categories', [ApiCategoryController::class, 'store']);
    Route::put('/categories/{category}', [ApiCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [ApiCategoryController::class, 'destroy']);

    Route::post('/menu-items', [ApiMenuItemController::class, 'store']);
    Route::put('/menu-items/{menuItem}', [ApiMenuItemController::class, 'update']);
    Route::post('/menu-items/{menuItem}/toggle-sold-out', [ApiMenuItemController::class, 'toggleSoldOut']);
    Route::post('/menu-items/{menuItem}/toggle-display-on-website', [ApiMenuItemController::class, 'toggleDisplayOnWebsite']);
    Route::post('/menu-items/{menuItem}/regenerate-barcode', [ApiMenuItemController::class, 'regenerateBarcode']);
    Route::delete('/menu-items/{menuItem}', [ApiMenuItemController::class, 'destroy']);

    Route::get('/orders/summary', [ApiOrderController::class, 'summary']);
    Route::get('/orders/export', [ApiOrderController::class, 'export']);
    Route::post('/orders/purge', [ApiOrderController::class, 'purge']);
});

// Authenticated Orders & Stock Alerts API
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/orders', [ApiOrderController::class, 'index']);
    Route::get('/orders/{order}', [ApiOrderController::class, 'show']);
    Route::post('/orders', [ApiOrderController::class, 'store']);
    Route::post('/orders/{order}/approve', [ApiOrderController::class, 'approve']);
    Route::delete('/orders/{order}', [ApiOrderController::class, 'destroy']);

    Route::get('/stock-alerts', [App\Http\Controllers\Commerce\ApiStockAlertController::class, 'getActiveAlerts']);
    Route::post('/stock-alerts/acknowledge-all', [App\Http\Controllers\Commerce\ApiStockAlertController::class, 'acknowledgeAll']);
    Route::post('/stock-alerts/{alert}/acknowledge', [App\Http\Controllers\Commerce\ApiStockAlertController::class, 'acknowledge']);
    Route::get('/stock-status/{item}', [App\Http\Controllers\Commerce\ApiStockAlertController::class, 'getStockStatus']);
});

// Admin Commerce Web Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Stock Alerts View
    Route::get('/stock-alerts', function () {
        return view('admin.stock-alerts');
    })->name('admin.stock-alerts');

    // Products & Barcodes
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products.index');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('admin.products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::patch('/products/{product}/toggle-display', [AdminController::class, 'toggleProductDisplay'])->name('admin.products.toggleDisplay');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

    // Orders Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'orderDetails'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.update-status');

    // Solutions / Product Catalog Management
    Route::resource('solutions', \App\Http\Controllers\Commerce\SolutionController::class, ['names' => [
        'index' => 'admin.solutions.index',
        'show' => 'admin.solutions.show',
        'create' => 'admin.solutions.create',
        'store' => 'admin.solutions.store',
        'edit' => 'admin.solutions.edit',
        'update' => 'admin.solutions.update',
        'destroy' => 'admin.solutions.destroy',
    ]]);
    Route::resource('solutions.items', \App\Http\Controllers\Commerce\SolutionItemController::class, [
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

// POS System API Routes
Route::prefix('api/pos')->group(function () {
    Route::get('/products', [App\Http\Controllers\Commerce\PosController::class, 'getProducts']);
});

Route::middleware('auth')->prefix('api/pos')->group(function () {
    Route::get('/barcode/{barcode}', [App\Http\Controllers\Commerce\PosController::class, 'lookupBarcode']);
    Route::get('/search/{query}', [App\Http\Controllers\Commerce\PosController::class, 'searchProducts']);
    Route::post('/complete-sale', [App\Http\Controllers\Commerce\PosController::class, 'completeSale']);
});

// Barcode Labels Management
Route::middleware('auth')->group(function () {
    Route::get('/barcode/{solutionItem}/view', [BarcodeController::class, 'view'])->name('barcode.view');
    Route::get('/barcode/{solutionItem}/download', [BarcodeController::class, 'download'])->name('barcode.download');
    Route::get('/barcode/{solutionItem}/download-image', [BarcodeController::class, 'downloadImage'])->name('barcode.download-image');
    Route::get('/barcode/{solutionItem}/svg', [BarcodeController::class, 'svg'])->name('barcode.svg');
    Route::get('/barcode/{solutionItem}/print', [BarcodeController::class, 'printLabel'])->name('barcode.print');
    Route::get('/barcode/{solutionItem}/info', [BarcodeController::class, 'metadata'])->name('barcode.metadata');
});

// POS Views
Route::middleware('auth')->group(function () {
    Route::get('/pos', function () {
        /** @var User|null $user */
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isPos())) {
            abort(403, 'Unauthorized');
        }
        return view('pos.index');
    })->name('pos.index');

    Route::get('/pos/receipt/{order}', function (App\Models\Order $order) {
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && $user->isPos() && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        return view('pos.receipt', compact('order'));
    })->name('pos.receipt');
});
