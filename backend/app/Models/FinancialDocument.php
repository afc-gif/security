<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinancialDocument extends Model
{
    use HasFactory;

    public const VISIBILITY_PRIVATE = 'private';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'uploaded_by',
        'file_path',
        'cloudinary_public_id',
        'cloudinary_resource_type',
        'file_name',
        'file_type',
        'file_size',
        'visibility',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
