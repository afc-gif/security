<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\JobRequestItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobInboxController extends Controller
{
    public function index(Request $request)
    {
        $statuses = [
            JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            JobRequestItem::STATUS_OPEN,
            JobRequestItem::STATUS_CLAIMED,
            JobRequestItem::STATUS_SUBMITTED,
            JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
            JobRequestItem::STATUS_APPROVED,
            JobRequestItem::STATUS_RETURNED,
            JobRequestItem::STATUS_REJECTED,
            JobRequestItem::STATUS_REOPENED,
            JobRequestItem::STATUS_OVERDUE,
            JobRequestItem::STATUS_CLOSED,
        ];

        $filters = [
            'client_id' => $request->input('client_id'),
            'status' => in_array($request->input('status'), $statuses, true) ? $request->input('status') : null,
            'converted' => in_array($request->input('converted'), ['converted', 'not_converted'], true) ? $request->input('converted') : 'all',
            'due_today' => $request->boolean('due_today'),
            'search' => trim((string) $request->input('search', '')),
        ];

        $baseQuery = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'claimer', 'project'])
            ->when($filters['client_id'], function (Builder $query, string $clientId) {
                $query->whereHas('jobRequest', fn (Builder $jobRequestQuery) => $jobRequestQuery->where('client_id', $clientId));
            })
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['converted'] === 'converted', fn (Builder $query) => $query->whereHas('project'))
            ->when($filters['converted'] === 'not_converted', fn (Builder $query) => $query->whereDoesntHave('project'))
            ->when($filters['due_today'], fn (Builder $query) => $query->whereDate('due_date', today()))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $search = $filters['search'];

                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('serviceCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('jobRequest', fn (Builder $jobRequestQuery) => $jobRequestQuery->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('jobRequest.client', function (Builder $clientQuery) use ($search) {
                            $clientQuery->where('client_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            });

        $summary = [
            'pending_review' => (clone $baseQuery)->where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)->count(),
            'overdue' => (clone $baseQuery)->where('status', JobRequestItem::STATUS_OVERDUE)->count(),
            'returned_reopened' => (clone $baseQuery)->whereIn('status', [JobRequestItem::STATUS_RETURNED, JobRequestItem::STATUS_REOPENED])->count(),
            'converted' => (clone $baseQuery)->whereHas('project')->count(),
        ];

        $sections = [
            'pendingReview' => (clone $baseQuery)->where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)->latest('submitted_at')->latest('id')->limit(10)->get(),
            'overdue' => (clone $baseQuery)->where('status', JobRequestItem::STATUS_OVERDUE)->latest('updated_at')->latest('id')->limit(10)->get(),
            'returnedReopened' => (clone $baseQuery)->whereIn('status', [JobRequestItem::STATUS_RETURNED, JobRequestItem::STATUS_REOPENED])->latest('updated_at')->latest('id')->limit(10)->get(),
            'approved' => (clone $baseQuery)->where('status', JobRequestItem::STATUS_APPROVED)->latest('updated_at')->latest('id')->limit(10)->get(),
            'converted' => (clone $baseQuery)->whereHas('project')->latest('updated_at')->latest('id')->limit(10)->get(),
            'recentlyActive' => (clone $baseQuery)->latest('updated_at')->latest('submitted_at')->latest('id')->limit(10)->get(),
        ];

        $groupedItems = (clone $baseQuery)
            ->latest('updated_at')
            ->latest('id')
            ->limit(100)
            ->get()
            ->groupBy(fn (JobRequestItem $item) => $item->jobRequest?->client?->client_name ?? 'Client unavailable')
            ->map(fn ($clientItems) => $clientItems->groupBy(fn (JobRequestItem $item) => $item->jobRequest?->title ?? 'Job request unavailable'));

        $clients = Client::query()
            ->orderBy('client_name')
            ->get(['id', 'client_name']);

        return view('admin.job-inbox.index', compact('clients', 'filters', 'groupedItems', 'sections', 'statuses', 'summary'));
    }
}
