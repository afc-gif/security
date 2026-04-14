<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\Task;

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
        $assignedProjects = Project::where('assigned_field_staff_id', $userId)->count();
        $ongoingProjects = Project::where('assigned_field_staff_id', $userId)
            ->where('status', 'ongoing')
            ->count();
        $completedProjects = Project::where('assigned_field_staff_id', $userId)
            ->where('status', 'completed')
            ->count();
        $assignedTasks = Task::where('assigned_to', $userId)->count();
        $pendingTasks = Task::where('assigned_to', $userId)
            ->where('status', 'pending')
            ->count();
        $completedTasks = Task::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->count();

        return view('field.dashboard', compact(
            'totalInspections',
            'completedInspections',
            'pendingInspections',
            'assignedProjects',
            'ongoingProjects',
            'completedProjects',
            'assignedTasks',
            'pendingTasks',
            'completedTasks'
        ));
    }
}
