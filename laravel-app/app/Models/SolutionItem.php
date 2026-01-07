<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolutionItem extends Model
{
    use HasFactory;

    protected $fillable = ['solution_id', 'product_id', 'name', 'barcode', 'description', 'price', 'stock', 'image', 'sort_order', 'active', 'is_sold_out'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'active' => 'boolean',
        'is_sold_out' => 'boolean',
    ];

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }
}
