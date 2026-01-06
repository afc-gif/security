<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index()
    {
        $products = SolutionItem::where('active', true)->paginate(12);
        return view('shop.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('shop.show', compact('product'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function cart()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        return view('shop.cart', compact('cart', 'total'));
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Product removed from cart!');
    }

    public function checkout(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login to checkout');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/cart')->with('error', 'Your cart is empty');
        }

        $totalAmount = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'total_amount' => $totalAmount,
            'status' => 'completed',
            'notes' => $request->input('notes'),
        ]);

        // Add order items
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            // Decrease stock
            $product = Product::find($item['id']);
            $product->stock -= $item['quantity'];
            $product->save();
        }

        session()->forget('cart');

        return redirect('/orders')->with('success', 'Order placed successfully!');
    }

    public function orders()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $orders = Auth::user()->orders()->with('items')->latest()->paginate(10);
        return view('shop.orders', compact('orders'));
    }

    public function orderDetails(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product');
        return view('shop.order-details', compact('order'));
    }
}
