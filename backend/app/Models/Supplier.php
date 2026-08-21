<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'finance_suppliers';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
    ];

    public function procurements(): HasMany
    {
        return $this->hasMany(FinancialProcurement::class, 'supplier_id');
    }

    public function materialCosts(): HasMany
    {
        return $this->hasMany(FinancialMaterialCost::class, 'supplier_id');
    }
}
