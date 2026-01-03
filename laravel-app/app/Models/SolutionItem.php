<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolutionItem extends Model
{
    use HasFactory;

    protected $fillable = ['solution_id', 'name', 'description', 'price', 'image', 'sort_order', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }
}
