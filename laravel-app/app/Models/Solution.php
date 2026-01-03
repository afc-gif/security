<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'description', 'sort_order', 'active'];

    public function items()
    {
        return $this->hasMany(SolutionItem::class)->orderBy('sort_order');
    }
}
