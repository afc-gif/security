<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;

class FieldDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $totalInspections = Inspection::where('assigned_to', $userId)->count();
        $completedInspections = Inspection::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->count();
        $pendingInspections = Inspection::where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'assigned'])
            ->count();

        return view('field.dashboard', compact(
            'totalInspections',
            'completedInspections',
            'pendingInspections'
        ));
    }
}
