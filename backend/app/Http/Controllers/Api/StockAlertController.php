<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockAlert;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function getActiveAlerts()
    {
        $alerts = StockAlert::with('solutionItem')
            ->whereNull('acknowledged_at')
            ->orderBy('alert_type', 'desc')
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

    public function acknowledge($alertId)
    {
        $alert = StockAlert::findOrFail($alertId);
        $alert->acknowledge(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Stock alert acknowledged',
        ]);
    }

    public function getStockStatus($itemId)
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
