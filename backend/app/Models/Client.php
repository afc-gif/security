<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city_state',
        'notes',
        'status',
    ];

    // Future relationships for inspections, projects, and tasks can live here.
}
