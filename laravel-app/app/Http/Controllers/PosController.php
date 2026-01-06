<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use Illuminate\Http\JsonResponse;

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
            // Search for solution item with matching barcode
            $solutionItem = SolutionItem::where('barcode', $barcode)->first();

            if (!$solutionItem) {
                return response()->json(
                    ['error' => 'Barcode not found'],
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
                'stock' => 999, // Unlimited for now
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
     * Get all products for POS catalog
     * 
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        try {
            $items = SolutionItem::where('barcode', '!=', null)
                ->select('id', 'barcode', 'product_name', 'name', 'price')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name ?? $item->name,
                        'sku' => $item->barcode,
                        'barcode' => $item->barcode,
                        'price' => (int) $item->price,
                        'stock' => 999,
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
     * Search products by name or barcode
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
            ->select('id', 'barcode', 'product_name', 'name', 'price')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name ?? $item->name,
                    'sku' => $item->barcode,
                    'barcode' => $item->barcode,
                    'price' => (int) $item->price,
                    'stock' => 999,
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
     * Complete a POS sale (create order from cart)
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
                'total' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,mobile'
            ]);

            // Create order (implement based on your Order model)
            // This is a placeholder - adjust based on your business logic
            
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => 'SALE-' . time(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Sale failed: ' . $e->getMessage()],
                500
            );
        }
    }
}
