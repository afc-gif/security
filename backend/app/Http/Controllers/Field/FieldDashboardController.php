<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Models\Project;

class FieldDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $availableJobsQuery = JobRequestItem::query()
            ->available()
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')
                    ->from('job_item_attempts')
                    ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                    ->where('job_item_attempts.user_id', $userId)
                    ->where('job_item_attempts.status', JobItemAttempt::STATUS_REJECTED);
            });

        $myJobsStatuses = [
            JobRequestItem::STATUS_CLAIMED,
            JobRequestItem::STATUS_SUBMITTED,
            JobRequestItem::STATUS_RETURNED,
            JobRequestItem::STATUS_OVERDUE,
        ];

        $availableJobsCount = (clone $availableJobsQuery)->count();

        $myJobsCount = JobRequestItem::query()
            ->where('claimed_by', $userId)
            ->whereIn('status', $myJobsStatuses)
            ->count();

        $overdueJobsCount = JobRequestItem::query()
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
            })
            ->count();

        $recentJobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->where('claimed_by', $userId)
            ->whereIn('status', $myJobsStatuses)
            ->latest('updated_at')
            ->latest('id')
            ->limit(4)
            ->get();

        $recentProjects = Project::query()
            ->with(['client', 'activeEditor'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'completed');
            })
            ->latest('updated_at')
            ->latest('id')
            ->limit(4)
            ->get();

        return view('field.dashboard', compact(
            'availableJobsCount',
            'myJobsCount',
            'overdueJobsCount',
            'recentJobs',
            'recentProjects'
        ));
    }
}
