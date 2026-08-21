<?php

namespace App\Models;

use App\Models\Concerns\HasFinancialContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FinancialMaterialCost extends Model
{
    use HasFactory;
    use HasFinancialContext;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'project_id',
        'inspection_id',
        'job_request_item_id',
        'original_context_type',
        'original_context_id',
        'material_name',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'incurred_on',
        'status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
        'updated_by',
        'supplier_id',
        'inventory_product_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'incurred_on' => 'date',
        'approved_at' => 'datetime',
    ];

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(FinancialDocument::class, 'documentable');
    }

    protected static function booted()
    {
        static::created(function (FinancialMaterialCost $materialCost) {
            if ($materialCost->inventory_product_id && $materialCost->status === self::STATUS_APPROVED) {
                $materialCost->inventoryProduct->decrement('current_stock', $materialCost->quantity);
            }
        });

        static::updated(function (FinancialMaterialCost $materialCost) {
            if ($materialCost->inventory_product_id) {
                $oldStatus = $materialCost->getOriginal('status');
                $newStatus = $materialCost->status;
                $oldQty = (float) $materialCost->getOriginal('quantity');
                $newQty = (float) $materialCost->quantity;

                if ($oldStatus !== self::STATUS_APPROVED && $newStatus === self::STATUS_APPROVED) {
                    $materialCost->inventoryProduct->decrement('current_stock', $newQty);
                } elseif ($oldStatus === self::STATUS_APPROVED && $newStatus !== self::STATUS_APPROVED) {
                    $materialCost->inventoryProduct->increment('current_stock', $oldQty);
                } elseif ($newStatus === self::STATUS_APPROVED) {
                    $qtyDiff = $newQty - $oldQty;
                    if ($qtyDiff != 0) {
                        $materialCost->inventoryProduct->decrement('current_stock', $qtyDiff);
                    }
                }
            }
        });

        static::deleted(function (FinancialMaterialCost $materialCost) {
            if ($materialCost->inventory_product_id && $materialCost->status === self::STATUS_APPROVED) {
                $materialCost->inventoryProduct->increment('current_stock', $materialCost->quantity);
            }
        });
    }
}
