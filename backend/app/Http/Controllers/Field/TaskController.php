<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignable')
            ->where('assigned_to', auth()->id())
            ->latest('due_date')
            ->latest('id')
            ->paginate(15);

        return view('field.tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $this->authorizeAssignedTask($task);

        $task->load('assignable');

        return view('field.tasks.show', compact('task'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeAssignedTask($task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $updates['completed_at'] = now();
        } else {
            $updates['completed_at'] = null;
        }

        $task->update($updates);

        return redirect()
            ->route('field.tasks.show', $task)
            ->with('success', 'Task status updated successfully.');
    }

    private function authorizeAssignedTask(Task $task): void
    {
        if ((int) $task->assigned_to !== (int) auth()->id()) {
            abort(403, 'Unauthorized task access');
        }
    }
}
