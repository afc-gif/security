<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    /**
     * Lookup product by barcode
     * 
     * @param string $barcode
     * @return JsonResponse
     */
    public function lookupBarcode(string $barcode): JsonResponse
    {
        try {
            // Search for solution item with matching barcode (database products only)
            $solutionItem = SolutionItem::where('barcode', $barcode)
                ->where('active', true)
                ->first();

            if (!$solutionItem) {
                return response()->json(
                    ['error' => 'Product not found'],
                    404
                );
            }

            // Return product data in format compatible with POS
            return response()->json([
                'id' => $solutionItem->id,
                'name' => $solutionItem->product_name ?? $solutionItem->name,
                'sku' => $solutionItem->barcode,
                'barcode' => $solutionItem->barcode,
                'price' => (int) $solutionItem->price,
                'stock' => $solutionItem->stock ?? 999,
                'category' => 'product',
                'emoji' => '📦'
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Lookup failed: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get all products for POS catalog (database products only)
     * 
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        try {
            $items = SolutionItem::where('barcode', '!=', null)
                ->where('active', true)
                ->select('id', 'barcode', 'product_name', 'name', 'price', 'stock')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name ?? $item->name,
                        'sku' => $item->barcode,
                        'barcode' => $item->barcode,
                        'price' => (int) $item->price,
                        'stock' => $item->stock ?? 999,
                        'category' => 'product',
                        'emoji' => '📦'
                    ];
                });

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Failed to fetch products: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Search products by name or barcode (database products only)
     * 
     * @param string $query
     * @return JsonResponse
     */
    public function searchProducts(string $query): JsonResponse
    {
        try {
            $items = SolutionItem::where(function ($q) use ($query) {
                $q->where('barcode', 'like', "%{$query}%")
                  ->orWhere('product_name', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('barcode', '!=', null)
            ->where('active', true)
            ->select('id', 'barcode', 'product_name', 'name', 'price', 'stock')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name ?? $item->name,
                    'sku' => $item->barcode,
                    'barcode' => $item->barcode,
                    'price' => (int) $item->price,
                    'stock' => $item->stock ?? 999,
                    'category' => 'product',
                    'emoji' => '📦'
                ];
            });

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Search failed: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Complete a POS sale (create order with salesperson info)
     * 
     * @return JsonResponse
     */
    public function completeSale(): JsonResponse
    {
        try {
            // Get cart data from request
            $cartData = request()->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,mobile'
            ]);

            // Create order with current user (POS operator) as the seller
            $order = Order::create([
                'user_id' => auth()->id(), // Current logged-in POS user
                'total_amount' => $cartData['total'],
                'status' => 'completed',
                'payment_method' => $cartData['payment_method'],
                'notes' => 'POS Sale'
            ]);

            // Create order items
            foreach ($cartData['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'solution_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Optional: Update stock
                $solutionItem = SolutionItem::find($item['id']);
                if ($solutionItem && $solutionItem->stock) {
                    $solutionItem->decrement('stock', $item['quantity']);
                }
            }

            Log::info('POS Sale Completed', [
                'order_id' => $order->id,
                'seller' => auth()->user()->name,
                'total' => $cartData['total'],
                'payment_method' => $cartData['payment_method']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => $order->id,
                'salesperson' => auth()->user()->name,
                'timestamp' => $order->created_at->toIso8601String(),
                'total' => $cartData['total'],
                'payment_method' => $cartData['payment_method']
            ]);
        } catch (\Exception $e) {
            Log::error('POS Sale Failed', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);
            
            return response()->json(
                ['error' => 'Sale failed: ' . $e->getMessage()],
                500
            );
        }
    }
}
