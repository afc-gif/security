<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                'name' => $solutionItem->name,
                'sku' => $solutionItem->barcode,
                'barcode' => $solutionItem->barcode,
                'price' => (int) $solutionItem->price,
                'stock' => $solutionItem->stock ?? 999,
                'category' => 'product'
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
            $items = SolutionItem::where('active', true)
                ->with('solution')
                ->select('id', 'solution_id', 'barcode', 'name', 'description', 'price', 'stock', 'image')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description ?? 'Enterprise-grade solution product',
                        'sku' => $item->barcode,
                        'barcode' => $item->barcode,
                        'price' => (float) $item->price,
                        'stock' => $item->stock ?? 999,
                        'image' => $item->image,
                        'solution' => [
                            'id' => $item->solution?->id,
                            'name' => $item->solution?->name ?? 'Solution'
                        ],
                        'category' => 'product'
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
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('barcode', '!=', null)
            ->where('active', true)
            ->select('id', 'barcode', 'name', 'price', 'stock')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->barcode,
                    'barcode' => $item->barcode,
                    'price' => (int) $item->price,
                    'stock' => $item->stock ?? 999,
                    'category' => 'product'
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

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($cartData) {
                // Create order with current user (POS operator) as the seller
                $order = Order::create([
                    'user_id' => auth()->id(), // Current logged-in POS user
                    'total_amount' => $cartData['total'],
                    'status' => 'completed',
                    'payment_method' => $cartData['payment_method'],
                    'notes' => 'POS Sale'
                ]);

                // Get all product IDs to fetch in one query
                $productIds = array_column($cartData['items'], 'id');
                $products = SolutionItem::whereIn('id', $productIds)->get()->keyBy('id');

                // Prepare order items and stock updates
                $orderItems = [];
                $stockUpdates = [];
                $alertsToCreate = [];

                foreach ($cartData['items'] as $item) {
                    // Create order item
                    $orderItems[] = [
                        'order_id' => $order->id,
                        'solution_item_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Calculate new stock
                    $solutionItem = $products->get($item['id']);
                    if ($solutionItem && $solutionItem->stock !== null) {
                        $currentStock = (int) $solutionItem->stock;
                        $newStock = max(0, $currentStock - (int) $item['quantity']);
                        
                        $stockUpdates[$item['id']] = [
                            'stock' => $newStock,
                            'is_sold_out' => $newStock === 0,
                        ];

                        // Check if we need to create alerts
                        if ($newStock === 0) {
                            $existingAlert = $solutionItem->stockAlerts()
                                ->where('alert_type', 'out_of_stock')
                                ->whereNull('acknowledged_at')
                                ->exists();
                            
                            if (!$existingAlert) {
                                $alertsToCreate[] = [
                                    'solution_item_id' => $item['id'],
                                    'alert_type' => 'out_of_stock',
                                    'threshold' => 0,
                                    'current_stock' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        } elseif ($newStock <= 2 && $newStock > 0) {
                            $existingAlert = $solutionItem->stockAlerts()
                                ->where('alert_type', 'low_stock')
                                ->where('threshold', 2)
                                ->whereNull('acknowledged_at')
                                ->exists();
                            
                            if (!$existingAlert) {
                                $alertsToCreate[] = [
                                    'solution_item_id' => $item['id'],
                                    'alert_type' => 'low_stock',
                                    'threshold' => 2,
                                    'current_stock' => $newStock,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }

                // Batch insert order items
                if (!empty($orderItems)) {
                    OrderItem::insert($orderItems);
                }

                // Update stock for all items
                foreach ($stockUpdates as $productId => $updates) {
                    SolutionItem::where('id', $productId)->update($updates);
                }

                // Batch create alerts
                if (!empty($alertsToCreate)) {
                    StockAlert::insert($alertsToCreate);
                }

                Log::info('POS Sale Completed', [
                    'order_id' => $order->id,
                    'seller' => auth()->user()->name,
                    'total' => $cartData['total'],
                    'payment_method' => $cartData['payment_method'],
                    'items_count' => count($cartData['items']),
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
            });
        } catch (\Exception $e) {
            Log::error('POS Sale Failed', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(
                ['error' => 'Sale failed: ' . $e->getMessage()],
                500
            );
        }
    }
}
