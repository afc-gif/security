<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\JobRequest;
use App\Models\JobRequestItem;

class OperationsOverviewController extends Controller
{
    public function index()
    {
        // Admin-actionable statuses (excludes STATUS_SUBMITTED which belongs to coordinator)
        $adminActionable = [
            JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
        ];

        $adminMonitored = [
            JobRequestItem::STATUS_OVERDUE,
            JobRequestItem::STATUS_RETURNED,
            JobRequestItem::STATUS_REOPENED,
            JobRequestItem::STATUS_PENDING_ASSIGNMENT,
        ];

        $summary = [
            'ready_for_review' => JobRequestItem::where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)->count(),
            'overdue'          => JobRequestItem::where('status', JobRequestItem::STATUS_OVERDUE)->count(),
            'needs_assignment' => JobRequestItem::where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)->count(),
            'in_progress'      => JobRequestItem::whereIn('status', [
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_SUBMITTED,
            ])->count(),
            'returned'         => JobRequestItem::where('status', JobRequestItem::STATUS_RETURNED)->count(),
            'approved'         => JobRequestItem::where('status', JobRequestItem::STATUS_APPROVED)->count(),
        ];

        // Items requiring admin attention — sorted by urgency
        $requiresAttention = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'claimer'])
            ->whereIn('status', [
                JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
                JobRequestItem::STATUS_OVERDUE,
                JobRequestItem::STATUS_RETURNED,
            ])
            ->orderByRaw("FIELD(status, 'pending_admin_review', 'overdue', 'returned')")
            ->latest('submitted_at')
            ->latest('id')
            ->limit(15)
            ->get();

        // Recently active pipeline items
        $recentlyActive = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'claimer'])
            ->whereIn('status', [
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_SUBMITTED,
                JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            ])
            ->latest('updated_at')
            ->latest('id')
            ->limit(10)
            ->get();

        // Job request summary
        $totalJobRequests = JobRequest::count();
        $openJobRequests  = JobRequest::where('status', 'open')->count();

        return view('admin.operations.overview', compact(
            'summary',
            'requiresAttention',
            'recentlyActive',
            'totalJobRequests',
            'openJobRequests',
        ));
    }
}
