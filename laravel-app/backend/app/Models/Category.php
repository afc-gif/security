<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'active',
    ];

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
