<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'solution_item_id',
        'quantity_changed',
        'transaction_type',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    public function solutionItem()
    {
        return $this->belongsTo(SolutionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
