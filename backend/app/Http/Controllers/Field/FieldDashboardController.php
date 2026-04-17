<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FieldDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $jobItemsAvailable = $this->hasTable('job_request_items')
            && $this->hasColumns('job_request_items', ['status', 'due_date', 'claimed_by']);
        $projectsAvailable = $this->hasTable('projects');

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
        $availableJobsCount = $jobItemsAvailable ? $this->safely(fn () => $this->availableJobsQuery($userId)
            ->count(), 0) : 0;
        $myJobsCount = $jobItemsAvailable ? $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_CLAIMED)
            ->count(), 0) : 0;
        $returnedJobsCount = $jobItemsAvailable ? $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_RETURNED)
            ->count(), 0) : 0;
        $overdueJobsCount = $jobItemsAvailable ? $this->safely(fn () => $this->overdueJobsQuery($userId)
            ->count(), 0) : 0;
        $dueTodayJobs = $jobItemsAvailable ? $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->whereDate('due_date', today())
            ->whereIn('status', [
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_RETURNED,
            ])
            ->count(), 0) : 0;
        $urgentJobs = $jobItemsAvailable ? $this->safely(fn () => $this->overdueJobsQuery($userId)
            ->with(['jobRequest.client', 'serviceCategory'])
            ->orWhere(function ($query) use ($userId) {
                $query->where('claimed_by', $userId)
                    ->where('status', JobRequestItem::STATUS_RETURNED);
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [JobRequestItem::STATUS_OVERDUE])
            ->orderBy('due_date')
            ->limit(4)
            ->get(), collect()) : collect();
        $recentJobs = $jobItemsAvailable ? $this->safely(fn () => JobRequestItem::query()
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
            ->get(), collect()) : collect();
        $recentProjects = $projectsAvailable ? $this->safely(fn () => $this->currentProjectsQuery()
            ->latest('updated_at')
            ->latest('id')
            ->limit(6)
            ->get(), collect()) : collect();
        $currentProjectsCount = $projectsAvailable ? $this->safely(fn () => $this->currentProjectsQuery()->count(), 0) : 0;

        $myClaimedJobs = $myJobsCount;
        $returnedJobs = $returnedJobsCount;
        $overdueJobs = $overdueJobsCount;
        $currentProjects = $recentProjects;

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
            'myJobsCount',
            'myClaimedJobs',
            'returnedJobsCount',
            'returnedJobs',
            'overdueJobsCount',
            'overdueJobs',
            'dueTodayJobs',
            'urgentJobs',
            'recentJobs',
            'recentProjects',
            'currentProjects',
            'currentProjectsCount'
        ));
    }

    private function availableJobsQuery(?int $userId): Builder
    {
        return JobRequestItem::query()
            ->available()
            ->when($userId, function ($query) use ($userId) {
                if ($this->hasTable('job_item_attempts')
                    && $this->hasColumns('job_item_attempts', ['job_request_item_id', 'user_id', 'status'])) {
                    $query->whereNotExists(function ($attemptQuery) use ($userId) {
                        $attemptQuery->selectRaw('1')
                            ->from('job_item_attempts')
                            ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                            ->where('job_item_attempts.user_id', $userId)
                            ->where('job_item_attempts.status', JobItemAttempt::STATUS_REJECTED);
                    });
                }
            });
    }

    private function overdueJobsQuery(?int $userId): Builder
    {
        return JobRequestItem::query()
            ->where('claimed_by', $userId)
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
            });
    }

    private function currentProjectsQuery(): Builder
    {
        $query = Project::query();

        if ($this->hasTable('clients')) {
            $query->with(['client']);
        }

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
        return $this->hasColumns('projects', [$column]);
    }

    private function hasTable(string $table): bool
    {
        return $this->safely(fn () => Schema::hasTable($table), false);
    }

    private function hasColumns(string $table, array $columns): bool
    {
        return $this->safely(fn () => collect($columns)
            ->every(fn ($column) => Schema::hasColumn($table, $column)), false);
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
