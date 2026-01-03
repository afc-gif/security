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
        $totalProducts = Product::count();
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|exists:solutions,name',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);
        $this->syncProductToSolutionItem($product);

        return redirect('/admin/products')->with('success', 'Product created successfully!');
    }

    public function editProduct(Product $product)
    {
        $categories = Solution::orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|exists:solutions,name',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        $this->syncProductToSolutionItem($product);

        return redirect('/admin/products')->with('success', 'Product updated successfully!');
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
                        'price' => $item->price ? 'R' . number_format($item->price, 2) : null,
                        'stock' => $item->stock,
                        'image' => $item->image ? asset('storage/' . $item->image) : null,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function syncProductToSolutionItem(Product $product): void
    {
        try {
            $solution = Solution::where('name', $product->category)->first();
            if (!$solution) {
                Log::warning("Solution not found for category: {$product->category}");
                return;
            }

            $item = SolutionItem::where('product_id', $product->id)->first();
            $barcode = null;
            
            if ($item && !empty($item->barcode)) {
                $barcode = $item->barcode;
            } else {
                // Generate unique barcode
                do {
                    $barcode = strtoupper(Str::random(10));
                } while (SolutionItem::where('barcode', $barcode)->exists());
            }

            $payload = [
                'solution_id' => $solution->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'barcode' => $barcode,
                'image' => $product->image,
                'active' => true,
            ];

            if ($item) {
                $item->update($payload);
            } else {
                SolutionItem::create($payload);
            }
        } catch (\Exception $e) {
            Log::error("Error syncing product to solution item: " . $e->getMessage());
            throw $e;
        }
    }

    private function removeSolutionItemForProduct(Product $product): void
    {
        $item = SolutionItem::where('product_id', $product->id)->first();
        if ($item) {
            $item->delete();
        }
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
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect('/admin/users')->with('success', 'User deleted successfully!');
    }
}
