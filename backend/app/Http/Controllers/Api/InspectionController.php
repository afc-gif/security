<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspection;

class InspectionController extends Controller
{
    public function index()
    {
        return Inspection::query()
            ->with(['client', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }
}
