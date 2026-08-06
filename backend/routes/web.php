<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\Field\FieldDashboardController;
use App\Http\Controllers\Field\InspectionController as FieldInspectionController;
use App\Http\Controllers\Field\JobController as FieldJobController;
use App\Http\Controllers\Field\ProjectController as FieldProjectController;
use App\Http\Controllers\Field\TaskController as FieldTaskController;
use App\Http\Controllers\FieldCoordinator\JobAssignmentController as CoordinatorJobAssignmentController;
use App\Http\Controllers\ShopController;
use App\Models\Installation;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\MenuItemController as ApiMenuItemController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\UserAdminController as ApiUserAdminController;
use App\Models\User;
use Illuminate\Support\Carbon;

// Authentication routes only
Route::middleware('web')->group(function () {
    Route::get('/', function () {
        $installations = collect();

        try {
            if (Schema::hasTable('installations')) {
                $installations = Installation::query()
                    ->where('is_public', true)
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->orderByDesc('completed_at')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Keep homepage functional even if database is temporarily unavailable.
        }

        return view('welcome', compact('installations'));
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

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ]);

    return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
});

Route::get('/sitemap.xml', function () {
    $now = Carbon::now()->toAtomString();
    $urls = [
        ['loc' => url('/'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => url('/solutions'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.9'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "    <url>\n";
        $xml .= '        <loc>' . e($url['loc']) . "</loc>\n";
        $xml .= '        <lastmod>' . e($url['lastmod']) . "</lastmod>\n";
        $xml .= '        <changefreq>' . e($url['changefreq']) . "</changefreq>\n";
        $xml .= '        <priority>' . e($url['priority']) . "</priority>\n";
        $xml .= "    </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
});

Route::prefix('api')->group(function () {
    Route::get('/health', fn () => ['status' => 'ok']);
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::get('/menu-items', [ApiMenuItemController::class, 'index']);
    Route::get('/menu-items/lookup', [ApiMenuItemController::class, 'lookup']);
    Route::get('/menu-items/search', [ApiMenuItemController::class, 'search']);
    Route::get('/solutions', [ShopController::class, 'solutionsApi']);
});

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

    Route::get('/users', [ApiUserAdminController::class, 'index']);
    Route::put('/users/{user}', [ApiUserAdminController::class, 'update']);
    Route::delete('/users/{user}', [ApiUserAdminController::class, 'destroy']);

    Route::get('/clients', [\App\Http\Controllers\Api\ClientController::class, 'index']);
    Route::get('/inspections', [\App\Http\Controllers\Api\InspectionController::class, 'index']);
});

Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/orders', [ApiOrderController::class, 'index']);
    Route::get('/orders/{order}', [ApiOrderController::class, 'show']);
    Route::post('/orders', [ApiOrderController::class, 'store']);
    Route::post('/orders/{order}/approve', [ApiOrderController::class, 'approve']);
    Route::delete('/orders/{order}', [ApiOrderController::class, 'destroy']);

    // Stock alerts endpoints (accessible to authenticated users)
    Route::get('/stock-alerts', [App\Http\Controllers\Api\StockAlertController::class, 'getActiveAlerts']);
    Route::post('/stock-alerts/acknowledge-all', [App\Http\Controllers\Api\StockAlertController::class, 'acknowledgeAll']);
    Route::post('/stock-alerts/{alert}/acknowledge', [App\Http\Controllers\Api\StockAlertController::class, 'acknowledge']);
    Route::get('/stock-status/{item}', [App\Http\Controllers\Api\StockAlertController::class, 'getStockStatus']);
});

// Admin routes
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Stock Alerts
    Route::get('/stock-alerts', function () {
        return view('admin.stock-alerts');
    })->name('admin.stock-alerts');

    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])
        ->name('admin.notifications.read');

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

    // Users Management (view pending, approve, assign roles)
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
    Route::patch('/users/{user}/approve/{role}', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::patch('/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Clients Management
    Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class)
        ->except(['show'])
        ->names('admin.clients');

    // Job Requests Management
    Route::get('/job-inbox', [\App\Http\Controllers\Admin\JobInboxController::class, 'index'])
        ->name('admin.job-inbox.index');
    Route::resource('job-requests', \App\Http\Controllers\Admin\JobRequestController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.job-requests');
    Route::get('/job-items/{jobItem}', [\App\Http\Controllers\Admin\JobItemController::class, 'show'])
        ->name('admin.job-items.show');
    Route::post('/job-items/{jobItem}/review', [\App\Http\Controllers\Admin\JobItemController::class, 'review'])
        ->name('admin.job-items.review');
    Route::post('/job-items/{jobItem}/reopen', [\App\Http\Controllers\Admin\JobItemController::class, 'reopen'])
        ->name('admin.job-items.reopen');
    Route::post('/job-items/{jobItem}/convert-to-project', [\App\Http\Controllers\Admin\JobItemController::class, 'convertToProject'])
        ->name('admin.job-items.convert-to-project');

    // Inspections Management
    Route::resource('inspections', \App\Http\Controllers\Admin\InspectionController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.inspections');
    Route::post('/inspections/{inspection}/review', [\App\Http\Controllers\Admin\InspectionController::class, 'review'])
        ->name('admin.inspections.review');
    Route::post('/inspections/{inspection}/convert-to-project', [\App\Http\Controllers\Admin\ProjectController::class, 'convertFromInspection'])
        ->name('admin.inspections.convert-to-project');

    // Projects Management
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.projects');
    Route::post('/projects/{project}/complete', [\App\Http\Controllers\Admin\ProjectController::class, 'complete'])
        ->name('admin.projects.complete');
    Route::post('/projects/{project}/reopen-work', [\App\Http\Controllers\Admin\ProjectController::class, 'reopenWork'])
        ->name('admin.projects.reopen-work');
    Route::post('/project-updates/{update}/review', [\App\Http\Controllers\Admin\ProjectController::class, 'reviewUpdate'])
        ->name('admin.project-updates.review');

    // Tasks Management
    Route::resource('tasks', \App\Http\Controllers\Admin\TaskController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('admin.tasks');

    // Field Reports
    Route::get('/field-reports', [\App\Http\Controllers\Admin\FieldReportController::class, 'index'])
        ->name('admin.field-reports.index');

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

    // Installations Gallery Management
    Route::resource('installations', \App\Http\Controllers\Admin\InstallationController::class)->names([
        'index' => 'admin.installations.index',
        'create' => 'admin.installations.create',
        'store' => 'admin.installations.store',
        'edit' => 'admin.installations.edit',
        'update' => 'admin.installations.update',
        'destroy' => 'admin.installations.destroy',
    ])->except(['show']);
});

Route::middleware(['auth', 'role:field_staff,field_coordinator'])->prefix('field')->group(function () {
    Route::get('/dashboard', [FieldDashboardController::class, 'index'])->name('field.dashboard');
    Route::get('/inspections', [FieldInspectionController::class, 'index'])->name('field.inspections.index');
    Route::get('/inspections/{inspection}', [FieldInspectionController::class, 'show'])->name('field.inspections.show');
    Route::post('/inspections/{inspection}/submit', [FieldInspectionController::class, 'submitReport'])->name('field.inspections.submit');
    Route::get('/jobs', [FieldJobController::class, 'index'])->name('field.jobs.index');
    Route::post('/jobs/{jobItem}/claim', [FieldJobController::class, 'claim'])->name('field.jobs.claim');
    Route::get('/jobs/{jobItem}', [FieldJobController::class, 'show'])->name('field.jobs.show');
    Route::post('/jobs/{jobItem}/submit', [FieldJobController::class, 'submit'])->name('field.jobs.submit');
    Route::get('/projects', [FieldProjectController::class, 'index'])->name('field.projects.index');
    Route::get('/projects/{project}', [FieldProjectController::class, 'show'])->name('field.projects.show');
    Route::post('/projects/{project}/start-update', [FieldProjectController::class, 'startUpdate'])->name('field.projects.start-update');
    Route::post('/projects/{project}/updates', [FieldProjectController::class, 'submitUpdate'])->name('field.projects.submit-update');
    Route::post('/projects/{project}/release-update', [FieldProjectController::class, 'releaseUpdate'])->name('field.projects.release-update');
    Route::patch('/projects/{project}/requirements/{requirement}', [FieldProjectController::class, 'updateRequirement'])->name('field.projects.requirements.update');
    Route::get('/tasks', [FieldTaskController::class, 'index'])->name('field.tasks.index');
    Route::get('/tasks/{task}', [FieldTaskController::class, 'show'])->name('field.tasks.show');
    Route::patch('/tasks/{task}/status', [FieldTaskController::class, 'updateStatus'])->name('field.tasks.update-status');
});

Route::middleware(['auth', 'role:field_coordinator'])->prefix('coordinator')->group(function () {
    Route::get('/jobs', [CoordinatorJobAssignmentController::class, 'index'])->name('coordinator.jobs.index');
    Route::post('/jobs/{jobItem}/assign', [CoordinatorJobAssignmentController::class, 'assign'])->name('coordinator.jobs.assign');
    Route::post('/jobs/{jobItem}/claim', [CoordinatorJobAssignmentController::class, 'claim'])->name('coordinator.jobs.claim');
    Route::post('/jobs/{jobItem}/release', [CoordinatorJobAssignmentController::class, 'release'])->name('coordinator.jobs.release');
    Route::post('/jobs/{jobItem}/review', [CoordinatorJobAssignmentController::class, 'review'])->name('coordinator.jobs.review');
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

// Admin API routes for dashboard
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/products/search', [App\Http\Controllers\Admin\SolutionItemController::class, 'search']);
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
        /** @var User|null $user */
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isPos())) {
            abort(403, 'Unauthorized');
        }
        return view('pos.index');
    })->name('pos.index');
    
    // Receipt view route
    Route::get('/pos/receipt/{order}', function (App\Models\Order $order) {
        // Ensure user can only view their own sales or admins can see all
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && $user->isPos() && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        return view('pos.receipt', compact('order'));
    })->name('pos.receipt');
});
