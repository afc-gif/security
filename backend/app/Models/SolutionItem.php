<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::creating(function (SolutionItem $item) {
            if (empty($item->barcode)) {
                $item->barcode = static::generateBarcode();
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
}
