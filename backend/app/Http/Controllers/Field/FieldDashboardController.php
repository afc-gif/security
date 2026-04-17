<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Throwable;

class FieldDashboardController extends Controller
{
    public function index()
    {
        try {
            $userId = auth()->id();
            
            // If job_request_items table doesn't exist, return empty dashboard
            if (!$this->tableExists('job_request_items')) {
                Log::info('FieldDashboard: job_request_items table does not exist, returning minimal view');
                return $this->emptyDashboard();
            }

            $availableJobsCount = $this->safely(fn () => $this->availableJobsQuery($userId)->count(), 0);
        $myJobsCount = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_CLAIMED)
            ->count(), 0);
        $returnedJobsCount = $this->safely(fn () => JobRequestItem::where('claimed_by', $userId)
            ->where('status', JobRequestItem::STATUS_RETURNED)
            ->count(), 0);
        $overdueJobsCount = $this->safely(fn () => $this->overdueJobsQuery($userId)->count(), 0);
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
        $recentProjects = $this->safely(fn () => $this->currentProjectsQuery()
            ->latest('updated_at')
            ->latest('id')
            ->limit(6)
            ->get(), collect());
        $currentProjectsCount = $this->safely(fn () => $this->currentProjectsQuery()->count(), 0);

        $totalInspections = 0;
        $completedInspections = 0;
        $pendingInspections = 0;
        $assignedProjects = $currentProjectsCount;
        $ongoingProjects = 0;
        $completedProjects = 0;
        $assignedTasks = 0;
        $pendingTasks = 0;
        $completedTasks = 0;

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
        } catch (Throwable $e) {
            Log::error('FieldDashboard index error: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->emptyDashboard();
        }
    }

    /**
     * Return an empty dashboard for error cases.
     */
    private function emptyDashboard()
    {
        return view('field.dashboard', [
            'totalInspections' => 0,
            'completedInspections' => 0,
            'pendingInspections' => 0,
            'assignedProjects' => 0,
            'ongoingProjects' => 0,
            'completedProjects' => 0,
            'assignedTasks' => 0,
            'pendingTasks' => 0,
            'completedTasks' => 0,
            'availableJobsCount' => 0,
            'myJobsCount' => 0,
            'myClaimedJobs' => 0,
            'returnedJobsCount' => 0,
            'returnedJobs' => 0,
            'overdueJobsCount' => 0,
            'overdueJobs' => 0,
            'dueTodayJobs' => 0,
            'urgentJobs' => collect(),
            'recentJobs' => collect(),
            'recentProjects' => collect(),
            'currentProjects' => collect(),
            'currentProjectsCount' => 0,
        ]);
    }

    private function availableJobsQuery(?int $userId): Builder
    {
        return JobRequestItem::query()
            ->available()
            ->when($userId, function ($query) use ($userId) {
                $query->whereNotExists(function ($attemptQuery) use ($userId) {
                    $attemptQuery->selectRaw('1')
                        ->from('job_item_attempts')
                        ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                        ->where('job_item_attempts.user_id', $userId)
                        ->where('job_item_attempts.status', JobItemAttempt::STATUS_REJECTED);
                });
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
        return Project::query()
            ->with(['client', 'activeEditor'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'completed');
            });
    }

    private function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
