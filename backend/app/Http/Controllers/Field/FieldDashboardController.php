<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FieldDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $totalInspections = $this->safely(fn () => Inspection::where('assigned_to', $userId)->count(), 0);
        $completedInspections = $this->safely(fn () => Inspection::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->count(), 0);
        $pendingInspections = $this->safely(fn () => Inspection::where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'assigned'])
            ->count(), 0);
        $assignedProjects = $this->safely(fn () => $this->projectHasColumn('assigned_field_staff_id')
            ? Project::where('assigned_field_staff_id', $userId)->count()
            : 0, 0);
        $ongoingProjects = $this->safely(fn () => $this->projectHasColumn('assigned_field_staff_id')
            ? Project::where('assigned_field_staff_id', $userId)
            ->where('status', 'ongoing')
            ->count()
            : 0, 0);
        $completedProjects = $this->safely(fn () => $this->projectHasColumn('assigned_field_staff_id')
            ? Project::where('assigned_field_staff_id', $userId)
            ->where('status', 'completed')
            ->count()
            : 0, 0);
        $assignedTasks = $this->safely(fn () => Task::where('assigned_to', $userId)->count(), 0);
        $pendingTasks = $this->safely(fn () => Task::where('assigned_to', $userId)
            ->where('status', 'pending')
            ->count(), 0);
        $completedTasks = $this->safely(fn () => Task::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->count(), 0);
        $availableJobsCount = $this->safely(fn () => JobRequestItem::query()
            ->available()
            ->when($userId, function ($query) use ($userId) {
                $query->whereNotExists(function ($attemptQuery) use ($userId) {
                    $attemptQuery->selectRaw('1')
                        ->from('job_item_attempts')
                        ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                        ->where('job_item_attempts.user_id', $userId)
                        ->where('job_item_attempts.status', \App\Models\JobItemAttempt::STATUS_REJECTED);
                });
            })
            ->count(), 0);
        $myClaimedJobs = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_CLAIMED)
            ->count(), 0);
        $returnedJobs = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_RETURNED)
            ->count(), 0);
        $overdueJobs = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where(function ($query) {
                $query->where('status', JobRequestItem::STATUS_OVERDUE)
                    ->orWhere(function ($overdueQuery) {
                        $overdueQuery->whereNotNull('due_date')
                            ->where('due_date', '<', now())
                            ->whereIn('status', [
                                JobRequestItem::STATUS_CLAIMED,
                                JobRequestItem::STATUS_RETURNED,
                            ]);
                    });
            })
            ->count(), 0);
        $dueTodayJobs = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->whereDate('due_date', today())
            ->whereIn('status', [
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_RETURNED,
            ])
            ->count(), 0);
        $urgentJobs = $this->safely(fn () => JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->where('claimed_by', $userId)
            ->where(function ($query) {
                $query->where('status', JobRequestItem::STATUS_RETURNED)
                    ->orWhere('status', JobRequestItem::STATUS_OVERDUE)
                    ->orWhere(function ($overdueQuery) {
                        $overdueQuery->whereNotNull('due_date')
                            ->where('due_date', '<', now())
                            ->whereIn('status', [
                                JobRequestItem::STATUS_CLAIMED,
                                JobRequestItem::STATUS_RETURNED,
                            ]);
                    });
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [JobRequestItem::STATUS_OVERDUE])
            ->orderBy('due_date')
            ->limit(4)
            ->get(), collect());
        $recentJobs = $this->safely(fn () => JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->where('claimed_by', $userId)
            ->whereIn('status', [
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_SUBMITTED,
                JobRequestItem::STATUS_APPROVED,
                JobRequestItem::STATUS_RETURNED,
                JobRequestItem::STATUS_OVERDUE,
            ])
            ->latest('updated_at')
            ->limit(4)
            ->get(), collect());
        $currentProjects = $this->safely(fn () => $this->currentProjectsQuery()
            ->latest('updated_at')
            ->latest('id')
            ->limit(6)
            ->get(), collect());
        $currentProjectsCount = $this->safely(fn () => $this->currentProjectsQuery()->count(), 0);

        return view('field.dashboard', compact(
            'totalInspections',
            'completedInspections',
            'pendingInspections',
            'assignedProjects',
            'ongoingProjects',
            'completedProjects',
            'assignedTasks',
            'pendingTasks',
            'completedTasks',
            'availableJobsCount',
            'myClaimedJobs',
            'returnedJobs',
            'overdueJobs',
            'dueTodayJobs',
            'urgentJobs',
            'recentJobs',
            'currentProjects',
            'currentProjectsCount'
        ));
    }

    private function currentProjectsQuery()
    {
        $query = Project::query()->with(['client']);

        if ($this->projectHasColumn('active_editor_id')) {
            $query->with(['activeEditor']);
        }

        return $query->where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', '!=', 'completed');
        });
    }

    private function projectHasColumn(string $column): bool
    {
        return $this->safely(fn () => Schema::hasColumn('projects', $column), false);
    }

    private function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            report($exception);

            return $fallback;
        }
    }
}
