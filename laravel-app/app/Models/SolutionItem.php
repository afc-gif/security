<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolutionItem extends Model
{
    use HasFactory;

    protected $fillable = ['solution_id', 'name', 'barcode', 'description', 'price', 'stock', 'image', 'sort_order', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }
}
