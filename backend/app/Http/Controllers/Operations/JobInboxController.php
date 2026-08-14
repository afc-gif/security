<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\JobRequestItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobInboxController extends Controller
{
    /**
     * Statuses that appear in the Admin Job Inbox.
     *
     * STATUS_SUBMITTED is excluded — that stage belongs to the Coordinator.
     * Admins only act on STATUS_PENDING_ADMIN_REVIEW and later.
     */
    private const ADMIN_VISIBLE_STATUSES = [
        JobRequestItem::STATUS_PENDING_ASSIGNMENT,
        JobRequestItem::STATUS_OPEN,
        JobRequestItem::STATUS_CLAIMED,
        JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
        JobRequestItem::STATUS_APPROVED,
        JobRequestItem::STATUS_RETURNED,
        JobRequestItem::STATUS_REJECTED,
        JobRequestItem::STATUS_REOPENED,
        JobRequestItem::STATUS_OVERDUE,
        JobRequestItem::STATUS_CLOSED,
    ];

    /**
     * Summary strip counts — admin-actionable statuses only.
     */
    private const SUMMARY_STATUSES = [
        'pending_review'   => JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
        'overdue'          => JobRequestItem::STATUS_OVERDUE,
        'needs_assignment' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
        'returned'         => JobRequestItem::STATUS_RETURNED,
    ];

    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $baseQuery = $this->buildQuery($filters);

        $summary = $this->buildSummary();

        /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
        $items = (clone $baseQuery)
            ->latest('updated_at')
            ->latest('id')
            ->paginate(25);

        $items->withQueryString();

        $clients = Client::query()
            ->orderBy('client_name')
            ->get(['id', 'client_name']);

        $filterableStatuses = self::ADMIN_VISIBLE_STATUSES;

        // Presentational mappings for the view
        $statusLabels = [
            'pending_assignment'   => 'Needs Assignment',
            'open'                 => 'Available to Claim',
            'claimed'              => 'In Progress',
            'submitted'            => 'With Coordinator',
            'pending_admin_review' => 'Ready for Review',
            'returned'             => 'Returned to Field',
            'approved'             => 'Approved',
            'rejected'             => 'Rejected',
            'reopened'             => 'Reopened',
            'overdue'              => 'Overdue',
            'closed'               => 'Closed',
        ];

        $statusBadgeClasses = [
            'pending_assignment'   => 'badge-purple',
            'open'                 => 'badge-blue',
            'claimed'              => 'badge-blue',
            'submitted'            => 'badge-yellow',
            'pending_admin_review' => 'badge-amber',
            'returned'             => 'badge-orange',
            'approved'             => 'badge-green',
            'rejected'             => 'badge-red',
            'reopened'             => 'badge-blue',
            'overdue'              => 'badge-red',
            'closed'               => 'badge-gray',
        ];

        $sortOptions = [
            'updated_at'   => 'Last Updated',
            'submitted_at' => 'Submitted Date',
            'due_date'     => 'Due Date',
        ];

        $activeStatus = $filters['status'];

        return view('admin.job-inbox.index', compact(
            'clients',
            'filters',
            'filterableStatuses',
            'items',
            'summary',
            'statusLabels',
            'statusBadgeClasses',
            'sortOptions',
            'activeStatus',
        ));
    }

    private function resolveFilters(Request $request): array
    {
        $requestedStatus = $request->input('status');
        $status = in_array($requestedStatus, self::ADMIN_VISIBLE_STATUSES, true)
            ? $requestedStatus
            : null;

        return [
            'status'      => $status,
            'client_id'   => $request->input('client_id'),
            'assigned'    => in_array($request->input('assigned'), ['assigned', 'unassigned'], true)
                ? $request->input('assigned')
                : null,
            'overdue_only'=> $request->boolean('overdue_only'),
            'due_today'   => $request->boolean('due_today'),
            'search'      => trim((string) $request->input('search', '')),
            'sort'        => in_array($request->input('sort'), ['updated_at', 'submitted_at', 'due_date'], true)
                ? $request->input('sort')
                : 'updated_at',
        ];
    }

    private function buildQuery(array $filters): Builder
    {
        $query = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'claimer', 'project'])
            ->whereIn('status', self::ADMIN_VISIBLE_STATUSES);

        // Status filter
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Client filter
        if ($filters['client_id']) {
            $query->whereHas('jobRequest', fn (Builder $q) =>
                $q->where('client_id', $filters['client_id'])
            );
        }

        // Assignment filter
        if ($filters['assigned'] === 'assigned') {
            $query->whereNotNull('claimed_by');
        } elseif ($filters['assigned'] === 'unassigned') {
            $query->whereNull('claimed_by');
        }

        // Overdue filter
        if ($filters['overdue_only']) {
            $query->where('status', JobRequestItem::STATUS_OVERDUE);
        }

        // Due today filter
        if ($filters['due_today']) {
            $query->whereDate('due_date', today());
        }

        // Search
        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('serviceCategory', fn (Builder $cq) =>
                      $cq->where('name', 'like', "%{$search}%")
                  )
                  ->orWhereHas('jobRequest', fn (Builder $jq) =>
                      $jq->where('title', 'like', "%{$search}%")
                  )
                  ->orWhereHas('jobRequest.client', fn (Builder $clq) =>
                      $clq->where('client_name', 'like', "%{$search}%")
                          ->orWhere('company_name', 'like', "%{$search}%")
                  );
            });
        }

        // Sorting
        $sort = $filters['sort'];
        if ($sort === 'submitted_at') {
            $query->orderByRaw('submitted_at IS NULL ASC')->orderBy('submitted_at', 'desc');
        } elseif ($sort === 'due_date') {
            $query->orderByRaw('due_date IS NULL ASC')->orderBy('due_date', 'asc');
        } else {
            $query->latest('updated_at');
        }

        return $query;
    }

    private function buildSummary(): array
    {
        // These counts are always global (unfiltered) to give the admin accurate totals
        return [
            'pending_review'   => JobRequestItem::where('status', JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)->count(),
            'overdue'          => JobRequestItem::where('status', JobRequestItem::STATUS_OVERDUE)->count(),
            'needs_assignment' => JobRequestItem::where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)->count(),
            'returned'         => JobRequestItem::where('status', JobRequestItem::STATUS_RETURNED)->count(),
        ];
    }
}
