<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\Task;
use Illuminate\Support\Collection;

class FieldReportController extends Controller
{
    public function index()
    {
        $pendingInspectionReviewsCount = Inspection::whereNotNull('submitted_at')
            ->where('review_status', 'pending_review')
            ->count();

        $projectUpdatesNeedingCorrectionCount = ProjectUpdate::where('review_status', 'needs_correction')
            ->count();

        $completedTasksCount = Task::where('status', 'completed')
            ->count();

        $pendingInspectionReviews = Inspection::with(['client', 'assignedUser'])
            ->whereNotNull('submitted_at')
            ->where('review_status', 'pending_review')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $recentInspectionSubmissions = Inspection::with(['assignedUser'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $recentProjectUpdates = ProjectUpdate::with(['project', 'user'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        $projectUpdatesNeedingCorrection = ProjectUpdate::with(['project', 'user'])
            ->where('review_status', 'needs_correction')
            ->latest('reviewed_at')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $recentlyCompletedTasks = Task::with(['assignable', 'assignee'])
            ->where('status', 'completed')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        $activityFeed = $this->buildActivityFeed(
            $recentInspectionSubmissions,
            $recentProjectUpdates,
            $recentlyCompletedTasks
        );

        $recentProjectUpdatesCount = $recentProjectUpdates->count();

        return view('admin.field-reports.index', compact(
            'pendingInspectionReviewsCount',
            'pendingInspectionReviews',
            'recentInspectionSubmissions',
            'recentProjectUpdatesCount',
            'recentProjectUpdates',
            'projectUpdatesNeedingCorrectionCount',
            'projectUpdatesNeedingCorrection',
            'completedTasksCount',
            'recentlyCompletedTasks',
            'activityFeed'
        ));
    }

    private function buildActivityFeed(Collection $inspections, Collection $updates, Collection $tasks): Collection
    {
        return collect()
            ->merge($inspections->map(fn (Inspection $inspection) => [
                'type' => 'Inspection Submission',
                'title' => trim($inspection->inspection_code . ' — ' . $inspection->title, ' —') ?: 'Inspection record unavailable',
                'user' => $inspection->assignedUser?->name ?? '—',
                'timestamp' => $inspection->submitted_at,
                'link' => route('admin.inspections.show', $inspection),
            ]))
            ->merge($updates->map(fn (ProjectUpdate $update) => [
                'type' => 'Project Update',
                'title' => ($update->project?->project_code ?? 'Project unavailable') . ' — ' . ($update->summary ?: 'Project update'),
                'user' => $update->user?->name ?? '—',
                'timestamp' => $update->created_at,
                'link' => $update->project ? route('admin.projects.show', $update->project) : null,
            ]))
            ->merge($tasks->map(fn (Task $task) => [
                'type' => 'Task Completed',
                'title' => $task->title ?: 'Task record unavailable',
                'user' => $task->assignee?->name ?? '—',
                'timestamp' => $task->completed_at,
                'link' => route('admin.tasks.show', $task),
            ]))
            ->filter(fn (array $item) => $item['timestamp'] !== null)
            ->sortByDesc('timestamp')
            ->take(15)
            ->values();
    }
}
