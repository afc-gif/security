<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\StockAlert;
use Illuminate\Http\Request;

class ApiStockAlertController extends Controller
{
    public function getActiveAlerts()
    {
        $alerts = StockAlert::with('solutionItem')
            ->whereNull('acknowledged_at')
            ->orderByRaw("CASE WHEN alert_type = 'out_of_stock' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn ($alert) => $alert->solutionItem !== null)
            ->map(fn ($alert) => [
                'id' => $alert->id,
                'product_id' => $alert->solution_item_id,
                'product_name' => $alert->solutionItem->name,
                'barcode' => $alert->solutionItem->barcode,
                'alert_type' => $alert->alert_type,
                'current_stock' => $alert->current_stock,
                'threshold' => $alert->threshold,
                'created_at' => $alert->created_at,
            ])
            ->values();

        return response()->json($alerts);
    }

    public function acknowledge(int $alertId)
    {
        $alert = StockAlert::findOrFail($alertId);
        $alert->acknowledge(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Stock alert acknowledged',
        ]);
    }

    public function acknowledgeAll()
    {
        $updated = StockAlert::query()
            ->whereNull('acknowledged_at')
            ->update([
                'acknowledged_at' => now(),
                'acknowledged_by' => auth()->id(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock alerts acknowledged',
            'updated' => $updated,
        ]);
    }

    public function getStockStatus(int $itemId)
    {
        $item = \App\Models\SolutionItem::findOrFail($itemId);

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'barcode' => $item->barcode,
            'stock' => $item->stock,
            'status' => $item->stock === 0 ? 'out_of_stock' : ($item->stock <= 5 ? 'low_stock' : 'available'),
        ]);
    }
}
