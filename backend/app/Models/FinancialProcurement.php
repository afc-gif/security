<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FinancialProcurement extends Model
{
    use HasFactory;

    protected $table = 'finance_procurements';

    protected $fillable = [
        'supplier_id',
        'inventory_product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'purchase_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(FinancialDocument::class, 'documentable');
    }

    protected static function booted()
    {
        static::created(function (FinancialProcurement $procurement) {
            $procurement->product->increment('current_stock', $procurement->quantity);
        });

        static::updated(function (FinancialProcurement $procurement) {
            $qtyDiff = $procurement->quantity - $procurement->getOriginal('quantity');
            if ($qtyDiff != 0) {
                $procurement->product->increment('current_stock', $qtyDiff);
            }
        });

        static::deleted(function (FinancialProcurement $procurement) {
            $procurement->product->decrement('current_stock', $procurement->quantity);
        });
    }
}
