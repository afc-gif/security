<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    use HasFactory;

    protected $table = 'project_media';

    protected $fillable = [
        'project_id',
        'project_update_id',
        'uploaded_by',
        'file_path',
        'cloudinary_public_id',
        'cloudinary_resource_type',
        'file_name',
        'file_type',
        'file_size',
        'caption',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectUpdate()
    {
        return $this->belongsTo(ProjectUpdate::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
