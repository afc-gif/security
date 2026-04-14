<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionMedia extends Model
{
    use HasFactory;

    protected $table = 'inspection_media';

    protected $fillable = [
        'inspection_id',
        'uploaded_by',
        'file_path',
        'cloudinary_public_id',
        'cloudinary_resource_type',
        'file_name',
        'file_type',
        'file_size',
        'caption',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
