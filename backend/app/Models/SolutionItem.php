<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SolutionItem extends Model
{
    use HasFactory;

    protected $fillable = ['solution_id', 'product_id', 'name', 'barcode', 'description', 'price', 'stock', 'image', 'sort_order', 'active', 'is_sold_out', 'display_on_website'];

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
        } elseif ($this->stock <= 5) {
            // Low stock alert
            $existingAlert = $this->stockAlerts()
                ->where('alert_type', 'low_stock')
                ->whereNull('acknowledged_at')
                ->first();

            if (!$existingAlert) {
                StockAlert::create([
                    'solution_item_id' => $this->id,
                    'alert_type' => 'low_stock',
                    'threshold' => 5,
                    'current_stock' => $this->stock,
                ]);
            }
        }
    }
}
