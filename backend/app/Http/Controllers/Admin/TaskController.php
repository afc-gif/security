<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with(['assignable', 'assignee'])
            ->latest()
            ->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $fieldStaff = User::query()
            ->where('role', 'field_staff')
            ->orderBy('name')
            ->get();

        $inspections = Inspection::query()
            ->with('client')
            ->latest()
            ->limit(100)
            ->get();

        $projects = Project::query()
            ->with('client')
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.tasks.create', compact('fieldStaff', 'inspections', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignable_type' => ['required', Rule::in([Inspection::class, Project::class])],
            'assignable_id' => 'required|integer',
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'field_staff'),
            ],
            'due_date' => 'nullable|date',
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        $this->findAssignableOrFail($validated['assignable_type'], (int) $validated['assignable_id']);

        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['assigned_by'] = $request->user()->id;
        $validated['completed_at'] = $validated['status'] === 'completed' ? now() : null;

        $task = Task::create($validated);

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['assignable', 'assignee', 'assigner']);

        return view('admin.tasks.show', compact('task'));
    }

    private function findAssignableOrFail(string $assignableType, int $assignableId): Inspection|Project
    {
        return match ($assignableType) {
            Inspection::class => Inspection::query()->findOrFail($assignableId),
            Project::class => Project::query()->findOrFail($assignableId),
        };
    }
}
