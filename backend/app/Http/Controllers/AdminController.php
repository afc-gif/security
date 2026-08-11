<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Client;
use App\Models\FinancePermission;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\Solution;
use App\Models\SolutionItem;
use App\Models\StockAlert;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User|null $user */
            $user = auth()->user();
            if (!$user || (!$user->isAdmin() && !($request->routeIs('admin.dashboard') && $user->isManager()))) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    // Dashboard
    public function dashboard()
    {
        $totalProducts = SolutionItem::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $totalUsers = User::where('role', 'user')->count();
        $recentOrders = Order::with('user')->latest()->limit(5)->get();
        $clientsCount = $this->dashboardPreviewValue(fn () => Client::count(), 0);
        $clientsPreview = $this->dashboardPreviewValue(fn () => Client::query()
            ->latest()
            ->limit(3)
            ->get(), collect());
        $jobRequestsCount = $this->dashboardPreviewValue(fn () => JobRequest::count(), 0);
        $jobRequestsPreview = $this->dashboardPreviewValue(fn () => JobRequest::query()
            ->with('client')
            ->latest()
            ->limit(3)
            ->get(), collect());
        $pendingReviewCount = $this->dashboardPreviewValue(fn () => JobRequestItem::query()
            ->where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)
            ->count(), 0);
        $overdueItemsCount = $this->dashboardPreviewValue(fn () => $this->overdueJobItemsQuery()->count(), 0);
        $jobInboxPreview = $this->dashboardPreviewValue(fn () => JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->where(function ($query) {
                $query->where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)
                    ->orWhere('status', JobRequestItem::STATUS_OVERDUE)
                    ->orWhere(function ($overdueQuery) {
                        $overdueQuery->whereNotNull('due_date')
                            ->where('due_date', '<', now())
                            ->whereIn('status', [
                                JobRequestItem::STATUS_OPEN,
                                JobRequestItem::STATUS_CLAIMED,
                                JobRequestItem::STATUS_RETURNED,
                                JobRequestItem::STATUS_REOPENED,
                            ]);
                    });
            })
            ->latest('updated_at')
            ->latest('id')
            ->limit(3)
            ->get(), collect());
        $activeProjectsCount = $this->dashboardPreviewValue(fn () => $this->activeProjectsQuery()->count(), 0);
        $projectsPreview = $this->dashboardPreviewValue(fn () => $this->activeProjectsQuery()
            ->with(['client', 'activeEditor'])
            ->latest('updated_at')
            ->latest('id')
            ->limit(3)
            ->get(), collect());

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'totalUsers',
            'recentOrders',
            'clientsCount',
            'clientsPreview',
            'jobRequestsCount',
            'jobRequestsPreview',
            'pendingReviewCount',
            'overdueItemsCount',
            'jobInboxPreview',
            'activeProjectsCount',
            'projectsPreview'
        ));
    }

    private function overdueJobItemsQuery()
    {
        return JobRequestItem::query()
            ->where(function ($query) {
                $query->where('status', JobRequestItem::STATUS_OVERDUE)
                    ->orWhere(function ($overdueQuery) {
                        $overdueQuery->whereNotNull('due_date')
                            ->where('due_date', '<', now())
                            ->whereIn('status', [
                                JobRequestItem::STATUS_OPEN,
                                JobRequestItem::STATUS_CLAIMED,
                                JobRequestItem::STATUS_RETURNED,
                                JobRequestItem::STATUS_REOPENED,
                            ]);
                    });
            });
    }

    private function activeProjectsQuery()
    {
        return Project::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'completed');
            });
    }

    private function dashboardPreviewValue(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            Log::warning('Admin dashboard preview data unavailable: ' . $exception->getMessage());

            return $fallback;
        }
    }

    // Products Management
    public function products()
    {
        $solutionProducts = $this->loadSolutionProducts();
        $products = collect(); // legacy DB list hidden per request

        return view('admin.products.index', compact('products', 'solutionProducts'));
    }

    public function createProduct()
    {
        $categories = Solution::orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function storeProduct(Request $request, CloudinaryImageService $cloudinary)
    {
        if ($schemaError = $this->ensureProductSchema()) {
            return $schemaError;
        }
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category' => 'required|string|exists:solutions,name',
                'barcode' => 'nullable|string|max:255|unique:solution_items,barcode',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'display_on_website' => 'sometimes|boolean',
            ]);
            $validated['display_on_website'] = $request->boolean('display_on_website');

            // Handle image upload with better error handling
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                if (!$image->isValid()) {
                    Log::error("Invalid image file uploaded", ['error' => $image->getErrorMessage()]);
                    return back()->withErrors(['image' => 'Invalid image file. Please try again.'])->withInput();
                }

                try {
                    $upload = $cloudinary->upload($image, 'products');
                    $validated['image'] = $upload['url'];
                    $validated['image_public_id'] = $upload['public_id'];
                    Log::info("Image stored successfully", ['path' => $validated['image']]);
                } catch (\Exception $e) {
                    Log::error("Image storage failed: " . $e->getMessage());
                    return back()->withErrors(['image' => 'Failed to upload image. Please check server permissions and try again.'])->withInput();
                }
            }

            $product = null;
            DB::transaction(function () use (&$product, $validated) {
                $product = Product::create($validated);
                Log::info("Product created", ['product_id' => $product->id, 'name' => $product->name]);
                $this->syncProductToSolutionItem($product, $validated['barcode'] ?? null);
            });

            // Check stock level and create alerts if needed
            if ($product) {
                $this->checkAndCreateStockAlert($product->stock, $product->id);
            }

            return redirect('/admin/products')->with('success', 'Product created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Product validation failed", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            $errorMessage = trim($e->getMessage());
            if ($errorMessage === '') {
                $errorMessage = 'Unknown error';
            }
            $errorMessage = sprintf('%s: %s', get_class($e), $errorMessage);
            Log::error("Unexpected error in storeProduct: " . $errorMessage, ['exception' => $e]);
            return back()->withErrors(['error' => 'Server error: ' . $errorMessage])->withInput();
        }
    }

    public function editProduct(Product $product)
    {
        $categories = Solution::orderBy('sort_order')->get();
        $solutionItem = SolutionItem::where('product_id', $product->id)->first();
        return view('admin.products.edit', compact('product', 'categories', 'solutionItem'));
    }

    public function updateProduct(Request $request, Product $product, CloudinaryImageService $cloudinary)
    {
        if ($schemaError = $this->ensureProductSchema()) {
            return $schemaError;
        }
        try {
            $oldStock = $product->stock;
            
            // Get the SolutionItem to validate barcode uniqueness properly
            $solutionItem = SolutionItem::where('product_id', $product->id)->first();
            $solutionItemId = $solutionItem ? $solutionItem->id : null;
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category' => 'required|string|exists:solutions,name',
                'barcode' => $solutionItemId ? 'nullable|string|max:255|unique:solution_items,barcode,' . $solutionItemId . ',id' : 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'display_on_website' => 'sometimes|boolean',
            ]);
            $validated['display_on_website'] = $request->boolean('display_on_website');

            // Handle image upload with better error handling
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                if (!$image->isValid()) {
                    Log::error("Invalid image file uploaded during update", ['error' => $image->getErrorMessage()]);
                    return back()->withErrors(['image' => 'Invalid image file. Please try again.'])->withInput();
                }

                // Delete old image if it exists
                if ($product->image_public_id) {
                    try {
                        $cloudinary->destroy($product->image_public_id);
                        Log::info("Old image deleted", ['public_id' => $product->image_public_id]);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete old image: " . $e->getMessage());
                    }
                } elseif ($product->image && !ImageUrl::isAbsolute($product->image) && Storage::disk('public')->exists($product->image)) {
                    try {
                        Storage::disk('public')->delete($product->image);
                        Log::info("Old image deleted", ['path' => $product->image]);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete old image: " . $e->getMessage());
                    }
                }

                try {
                    $upload = $cloudinary->upload($image, 'products');
                    $validated['image'] = $upload['url'];
                    $validated['image_public_id'] = $upload['public_id'];
                    Log::info("New image stored successfully", ['path' => $validated['image']]);
                } catch (\Exception $e) {
                    Log::error("Image storage failed during update: " . $e->getMessage());
                    return back()->withErrors(['image' => 'Failed to upload image. Please check server permissions and try again.'])->withInput();
                }
            }

            DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
                Log::info("Product updated", ['product_id' => $product->id]);
                $this->syncProductToSolutionItem($product, $validated['barcode'] ?? null);
            });

            // Check if stock was changed and create alerts if needed
            if ($oldStock != $validated['stock']) {
                $this->checkAndCreateStockAlert($validated['stock'], $product->id);
            }

            return redirect('/admin/products')->with('success', 'Product updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Product validation failed during update", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            $errorMessage = trim($e->getMessage());
            if ($errorMessage === '') {
                $errorMessage = 'Unknown error';
            }
            $errorMessage = sprintf('%s: %s', get_class($e), $errorMessage);
            Log::error("Unexpected error in updateProduct: " . $errorMessage, ['exception' => $e]);
            return back()->withErrors(['error' => 'Server error: ' . $errorMessage])->withInput();
        }
    }

    public function deleteProduct(Product $product, CloudinaryImageService $cloudinary)
    {
        if ($product->image_public_id) {
            $cloudinary->destroy($product->image_public_id);
        } elseif ($product->image && !ImageUrl::isAbsolute($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $this->removeSolutionItemForProduct($product);
        $product->delete();
        return redirect('/admin/products')->with('success', 'Product deleted successfully!');
    }

    /**
     * Read solution categories/products for admin visibility.
     * Uses database (admin-managed) only, so CRUD actions are always available.
     */
    private function loadSolutionProducts(): array
    {
        // Database-driven solutions & items
        $dbSolutions = Solution::with(['items' => function ($query) {
            $query->orderBy('sort_order');
        }])->where('active', true)->orderBy('sort_order')->get();

        return $dbSolutions->map(function ($solution) {
            return [
                'id' => $solution->id,
                'title' => $solution->name,
                'description' => $solution->description,
                'items' => $solution->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'solution_id' => $item->solution_id,
                        'barcode' => $item->barcode,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price ? '₦' . number_format($item->price, 2) : null,
                        'stock' => $item->stock,
                        'is_sold_out' => (bool) $item->is_sold_out,
                        'image' => ImageUrl::url($item->image),
                        'display_on_website' => (bool) $item->display_on_website,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function syncProductToSolutionItem(Product $product, ?string $barcode = null): void
    {
        $solution = Solution::where('name', $product->category)->first();
        if (!$solution) {
            throw new \RuntimeException("Solution not found for category: {$product->category}");
        }

        $item = SolutionItem::where('product_id', $product->id)->first();
        $payload = [
            'solution_id' => $solution->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => $product->image,
            'image_public_id' => $product->image_public_id,
            'display_on_website' => (bool) ($product->display_on_website ?? true),
            'active' => true,
        ];

        // Use provided barcode if given, otherwise auto-generate for new items
        if (!$item) {
            $payload['barcode'] = !empty($barcode) ? $barcode : SolutionItem::generateBarcode();
        } elseif (!empty($barcode)) {
            // Update barcode if provided for existing item
            $payload['barcode'] = $barcode;
        }
        // If updating and no barcode provided, keep the existing one

        if ($item) {
            $item->update($payload);
            Log::info("SolutionItem updated", ['item_id' => $item->id, 'barcode' => $item->barcode]);
        } else {
            $createdItem = SolutionItem::create($payload);
            Log::info("SolutionItem created", ['item_id' => $createdItem->id, 'barcode' => $createdItem->barcode]);
        }
    }

    private function removeSolutionItemForProduct(Product $product): void
    {
        $item = SolutionItem::where('product_id', $product->id)->first();
        if ($item) {
            $item->delete();
        }
    }

    private function ensureProductSchema(): ?\Illuminate\Http\RedirectResponse
    {
        $missingTables = [];
        foreach (['products', 'solutions', 'solution_items'] as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (!empty($missingTables)) {
            return back()->withErrors([
                'error' => 'Database is missing required tables: ' . implode(', ', $missingTables) . '. Run migrations.'
            ])->withInput();
        }

        $requiredColumns = [
            'solution_items' => ['solution_id', 'product_id', 'name', 'price', 'stock', 'barcode', 'image', 'active'],
        ];

        $missingColumns = [];
        foreach ($requiredColumns as $table => $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $missingColumns[] = $table . '.' . $column;
                }
            }
        }

        if (!empty($missingColumns)) {
            return back()->withErrors([
                'error' => 'Database schema is missing required columns: ' . implode(', ', $missingColumns) . '. Run migrations.'
            ])->withInput();
        }

        if (Solution::count() === 0) {
            return back()->withErrors([
                'error' => 'No solution categories found. Seed the solutions table before creating products.'
            ])->withInput();
        }

        return null;
    }

    // Orders Management
    public function orders()
    {
        $orders = Order::with('user')->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function orderDetails(Order $order)
    {
        $order->load('items.product', 'items.solutionItem', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully!');
    }

    // Users Management
    public function users()
    {
        $approvedUsers = User::where('status', 'approved')
            ->with('financePermissions')
            ->paginate(15);
        $pendingCount = User::where('status', 'pending')->count();
        $financePermissions = FinancePermission::query()
            ->orderBy('id')
            ->get();

        return view('admin.users.index', compact('approvedUsers', 'pendingCount', 'financePermissions'));
    }

    // Pending Users (waiting for approval)
    public function pendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')->paginate(15);
        return view('admin.users.pending', compact('pendingUsers'));
    }

    // Approve user and assign role
    public function approveUser(User $user, $role)
    {
        if (!in_array($role, ['admin', 'manager', 'field_staff', 'field_coordinator', 'pos', 'user'], true)) {
            return back()->withErrors('Invalid role specified.');
        }

        $user->update([
            'status' => 'approved',
            'role' => $role,
        ]);

        Log::info('User approved and role assigned', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $role,
        ]);

        return back()->with('success', "User {$user->name} approved as {$role}!");
    }

    public function updateFinancePermissions(Request $request, User $user)
    {
        $validPermissions = FinancePermission::query()->pluck('slug')->all();
        $validated = $request->validate([
            'finance_permissions' => ['nullable', 'array'],
            'finance_permissions.*' => ['string', Rule::in($validPermissions)],
        ]);

        $permissionIds = FinancePermission::query()
            ->whereIn('slug', $validated['finance_permissions'] ?? [])
            ->pluck('id')
            ->all();

        $syncPayload = collect($permissionIds)
            ->mapWithKeys(fn (int $id) => [$id => [
                'granted_by' => $request->user()->id,
                'granted_at' => now(),
            ]])
            ->all();

        $user->financePermissions()->sync($syncPayload);

        Log::info('User finance permissions updated', [
            'user_id' => $user->id,
            'updated_by' => $request->user()->id,
            'permissions' => $validated['finance_permissions'] ?? [],
        ]);

        return back()->with('success', "Finance permissions updated for {$user->name}.");
    }

    // Reject pending user
    public function rejectUser(User $user)
    {
        $user->delete();

        Log::info('Pending user rejected', [
            'email' => $user->email,
        ]);

        return back()->with('success', 'User registration rejected and removed.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect('/admin/users')->with('success', 'User deleted successfully!');
    }

    /**
     * Check stock level and create alerts for admin and POS
     * - Alert when stock is 2 or below (low stock)
     * - Alert when stock is 0 (out of stock)
     */
    private function checkAndCreateStockAlert($stock, $productId): void
    {
        try {
            // Get the SolutionItem for this product
            $solutionItem = SolutionItem::where('product_id', $productId)->first();
            
            if (!$solutionItem) {
                Log::warning("SolutionItem not found for product", ['product_id' => $productId]);
                return;
            }

            // Check for out of stock (stock = 0)
            if ($stock === 0) {
                // Mark item as sold out
                $solutionItem->update(['is_sold_out' => true]);

                // Create out of stock alert if one doesn't already exist
                $existingAlert = $solutionItem->stockAlerts()
                    ->where('alert_type', 'out_of_stock')
                    ->whereNull('acknowledged_at')
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'solution_item_id' => $solutionItem->id,
                        'alert_type' => 'out_of_stock',
                        'threshold' => 0,
                        'current_stock' => 0,
                        'created_by' => auth()->id(),
                    ]);

                    Log::info("Out of stock alert created", [
                        'solution_item_id' => $solutionItem->id,
                        'product_name' => $solutionItem->name,
                    ]);
                }
            }
            // Check for low stock (stock is 1 or 2)
            elseif ($stock <= 2 && $stock > 0) {
                // Mark item as not sold out (if it was)
                if ($solutionItem->is_sold_out) {
                    $solutionItem->update(['is_sold_out' => false]);
                }

                // Create low stock alert if one doesn't already exist
                $existingAlert = $solutionItem->stockAlerts()
                    ->where('alert_type', 'low_stock')
                    ->where('threshold', 2)
                    ->whereNull('acknowledged_at')
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'solution_item_id' => $solutionItem->id,
                        'alert_type' => 'low_stock',
                        'threshold' => 2,
                        'current_stock' => $stock,
                        'created_by' => auth()->id(),
                    ]);

                    Log::info("Low stock alert created", [
                        'solution_item_id' => $solutionItem->id,
                        'product_name' => $solutionItem->name,
                        'current_stock' => $stock,
                    ]);
                }
            }
            // If stock is above 2, clear the is_sold_out flag
            elseif ($stock > 2) {
                if ($solutionItem->is_sold_out) {
                    $solutionItem->update(['is_sold_out' => false]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in checkAndCreateStockAlert: " . $e->getMessage(), [
                'product_id' => $productId,
                'exception' => $e,
            ]);
        }
    }

    // Toggle product display on website
    public function toggleProductDisplay(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            $validated = $request->validate([
                'display_on_website' => 'required|boolean'
            ]);
            
            $product->update(['display_on_website' => $validated['display_on_website']]);
            SolutionItem::where('product_id', $product->id)->update([
                'display_on_website' => $validated['display_on_website'],
            ]);
            
            return response()->json([
                'success' => true,
                'display_on_website' => $product->display_on_website
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error toggling product display: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product display status'
            ], 500);
        }
    }
}
