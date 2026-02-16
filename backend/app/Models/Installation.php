<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'city',
        'client_type',
        'completed_at',
        'cover_image',
        'cover_image_public_id',
        'gallery_images',
        'gallery_image_public_ids',
        'summary',
        'outcome',
        'sort_order',
        'is_featured',
        'is_public',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'gallery_images' => 'array',
        'gallery_image_public_ids' => 'array',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
    ];
}
