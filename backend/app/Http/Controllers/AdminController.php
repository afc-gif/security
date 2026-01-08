<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Solution;
use App\Models\SolutionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User|null $user */
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
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

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'totalUsers',
            'recentOrders'
        ));
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

    public function storeProduct(Request $request)
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            // Handle image upload with better error handling
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                if (!$image->isValid()) {
                    Log::error("Invalid image file uploaded", ['error' => $image->getErrorMessage()]);
                    return back()->withErrors(['image' => 'Invalid image file. Please try again.'])->withInput();
                }

                try {
                    // Store the image in the public disk under products folder
                    $path = $image->store('products', 'public');
                    $validated['image'] = $path;
                    Log::info("Image stored successfully", ['path' => $path]);
                } catch (\Exception $e) {
                    Log::error("Image storage failed: " . $e->getMessage());
                    return back()->withErrors(['image' => 'Failed to upload image. Please check server permissions and try again.'])->withInput();
                }
            }

            $product = null;
            DB::transaction(function () use (&$product, $validated) {
                $product = Product::create($validated);
                Log::info("Product created", ['product_id' => $product->id, 'name' => $product->name]);
                $this->syncProductToSolutionItem($product);
            });

            return redirect('/admin/products')->with('success', 'Product created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Product validation failed", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Unexpected error in storeProduct: " . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Server error: ' . $e->getMessage()])->withInput();
        }
    }

    public function editProduct(Product $product)
    {
        $categories = Solution::orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            // Handle image upload with better error handling
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                if (!$image->isValid()) {
                    Log::error("Invalid image file uploaded during update", ['error' => $image->getErrorMessage()]);
                    return back()->withErrors(['image' => 'Invalid image file. Please try again.'])->withInput();
                }

                // Delete old image if it exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    try {
                        Storage::disk('public')->delete($product->image);
                        Log::info("Old image deleted", ['path' => $product->image]);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete old image: " . $e->getMessage());
                    }
                }

                try {
                    $path = $image->store('products', 'public');
                    $validated['image'] = $path;
                    Log::info("New image stored successfully", ['path' => $path]);
                } catch (\Exception $e) {
                    Log::error("Image storage failed during update: " . $e->getMessage());
                    return back()->withErrors(['image' => 'Failed to upload image. Please check server permissions and try again.'])->withInput();
                }
            }

            DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
                Log::info("Product updated", ['product_id' => $product->id]);
                $this->syncProductToSolutionItem($product);
            });

            return redirect('/admin/products')->with('success', 'Product updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Product validation failed during update", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Unexpected error in updateProduct: " . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Server error: ' . $e->getMessage()])->withInput();
        }
    }

    public function deleteProduct(Product $product)
    {
        if ($product->image) {
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
                        'solution_id' => $item->solution_id,
                        'barcode' => $item->barcode,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price ? '₦' . number_format($item->price, 2) : null,
                        'stock' => $item->stock,
                        'image' => $item->image ? asset('storage/' . $item->image) : null,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function syncProductToSolutionItem(Product $product): void
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
            'active' => true,
        ];

        if (!$item) {
            $payload['barcode'] = SolutionItem::generateBarcode();
        }

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
        $order->load('items.product', 'user');
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
        $approvedUsers = User::where('status', 'approved')->paginate(15);
        $pendingCount = User::where('status', 'pending')->count();
        return view('admin.users.index', compact('approvedUsers', 'pendingCount'));
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
        if (!in_array($role, ['admin', 'pos'])) {
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
}
