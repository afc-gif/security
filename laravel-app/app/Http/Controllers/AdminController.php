<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
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
        $products = Product::paginate(15);
        $solutionProducts = $this->loadSolutionProducts();

        return view('admin.products.index', compact('products', 'solutionProducts'));
    }

    public function createProduct()
    {
        $categories = Category::orderBy('sort_order')->get(['id', 'name']);
        return view('admin.products.create', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect('/admin/products')->with('success', 'Product created successfully!');
    }

    public function editProduct(Product $product)
    {
        $categories = Category::orderBy('sort_order')->get(['id', 'name']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect('/admin/products')->with('success', 'Product updated successfully!');
    }

    public function deleteProduct(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect('/admin/products')->with('success', 'Product deleted successfully!');
    }

    /**
     * Read product/category data directly from public/solutions.html for admin visibility.
     * This keeps the admin view in sync with the live marketing page without touching the DB.
     */
    private function loadSolutionProducts(): array
    {
        $path = public_path('solutions.html');
        if (!File::exists($path)) {
            return [];
        }

        $html = File::get($path);
        $previousLibxmlSetting = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        if (!$dom->loadHTML($html)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $categories = [];

        foreach ($xpath->query('//section[contains(@class,"solution-section")]') as $section) {
            $titleNode = $xpath->query('.//h2', $section)->item(0);
            $descriptionNode = $xpath->query('.//div[contains(@class,"solution-section-header")]//p', $section)->item(0);

            $category = [
                'id' => $section->getAttribute('id') ?: null,
                'title' => $titleNode ? trim($titleNode->textContent) : 'Untitled Section',
                'description' => $descriptionNode ? trim($descriptionNode->textContent) : null,
                'items' => [],
            ];

            foreach ($xpath->query('.//div[contains(@class,"product-card")]', $section) as $card) {
                $nameNode = $xpath->query('.//h3[contains(@class,"product-name")]', $card)->item(0);
                $descriptionNode = $xpath->query('.//p[contains(@class,"product-description")]', $card)->item(0);
                $priceNode = $xpath->query('.//div[contains(@class,"product-price")]', $card)->item(0);
                $imageNode = $xpath->query('.//div[contains(@class,"product-image")]//img', $card)->item(0);
                $specNodes = $xpath->query('.//div[contains(@class,"product-specs")]//span', $card);

                $specs = [];
                if ($specNodes) {
                    foreach ($specNodes as $specNode) {
                        $specText = trim($specNode->textContent);
                        if ($specText !== '') {
                            $specs[] = $specText;
                        }
                    }
                }

                $category['items'][] = [
                    'name' => $nameNode ? trim($nameNode->textContent) : 'Unnamed Product',
                    'description' => $descriptionNode ? trim($descriptionNode->textContent) : '',
                    'price' => $priceNode ? trim($priceNode->textContent) : null,
                    'image' => ($imageNode && $imageNode->hasAttribute('src')) ? $imageNode->getAttribute('src') : null,
                    'specs' => $specs,
                ];
            }

            if (count($category['items']) > 0) {
                $categories[] = $category;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlSetting);

        return $categories;
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
