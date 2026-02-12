<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SolutionItem extends Model
{
    use HasFactory;

    protected $fillable = ['solution_id', 'product_id', 'name', 'barcode', 'description', 'price', 'stock', 'image', 'image_public_id', 'sort_order', 'active', 'is_sold_out', 'display_on_website'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'active' => 'boolean',
        'is_sold_out' => 'boolean',
        'display_on_website' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (SolutionItem $item) {
            if (empty($item->barcode)) {
                $item->barcode = static::generateBarcode();
            }
            if ($item->stock === null) {
                $item->stock = 0;
            }
        });

        static::created(function (SolutionItem $item) {
            // Check and create stock alerts after item is created
            $item->checkAndCreateStockAlert();
            // Update is_sold_out flag
            if ($item->stock === 0) {
                $item->update(['is_sold_out' => true]);
            }
        });

        static::updated(function (SolutionItem $item) {
            // Check and create stock alerts when stock is updated
            if ($item->isDirty('stock')) {
                $item->checkAndCreateStockAlert();
                // Update is_sold_out flag
                if ($item->stock === 0) {
                    $item->update(['is_sold_out' => true]);
                } elseif ($item->stock > 0 && $item->is_sold_out) {
                    $item->update(['is_sold_out' => false]);
                }
            }
        });
    }

    public static function generateBarcode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (static::where('barcode', $code)->exists());

        return $code;
    }

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    public function recordStockTransaction($quantity, $type, $referenceType = null, $referenceId = null, $userId = null, $notes = null)
    {
        return StockTransaction::create([
            'solution_item_id' => $this->id,
            'quantity_changed' => $quantity,
            'transaction_type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }

    public function checkAndCreateStockAlert()
    {
        try {
            if ($this->stock === 0) {
                // Check if alert already exists and is not acknowledged
                $existingAlert = $this->stockAlerts()
                    ->where('alert_type', 'out_of_stock')
                    ->whereNull('acknowledged_at')
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'solution_item_id' => $this->id,
                        'alert_type' => 'out_of_stock',
                        'threshold' => 0,
                        'current_stock' => 0,
                    ]);
                }
            } elseif ($this->stock <= 2 && $this->stock > 0) {
                // Low stock alert for stock 1-2
                $existingAlert = $this->stockAlerts()
                    ->where('alert_type', 'low_stock')
                    ->where('threshold', 2)
                    ->whereNull('acknowledged_at')
                    ->first();

                if (!$existingAlert) {
                    StockAlert::create([
                        'solution_item_id' => $this->id,
                        'alert_type' => 'low_stock',
                        'threshold' => 2,
                        'current_stock' => $this->stock,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in checkAndCreateStockAlert: " . $e->getMessage(), [
                'item_id' => $this->id,
                'exception' => $e,
            ]);
        }
    }
}
