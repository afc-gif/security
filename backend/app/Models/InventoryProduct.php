<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryProduct extends Model
{
    use HasFactory;

    protected $table = 'finance_inventory_products';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
    ];

    public function procurements(): HasMany
    {
        return $this->hasMany(FinancialProcurement::class, 'inventory_product_id');
    }

    public function materialCosts(): HasMany
    {
        return $this->hasMany(FinancialMaterialCost::class, 'inventory_product_id');
    }
}
