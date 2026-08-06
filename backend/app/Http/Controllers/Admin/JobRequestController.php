<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobRequestController extends Controller
{
    public function index()
    {
        $jobRequests = JobRequest::with('client')
            ->withCount('items')
            ->withCount([
                'items as overdue_items_count' => fn ($query) => $query
                    ->where(function ($overdueQuery) {
                        $overdueQuery->where('status', JobRequestItem::STATUS_OVERDUE)
                            ->orWhere(function ($dateQuery) {
                                $dateQuery->whereNotNull('due_date')
                                    ->where('due_date', '<', now())
                                    ->whereIn('status', [
                                        JobRequestItem::STATUS_OPEN,
                                        JobRequestItem::STATUS_CLAIMED,
                                        JobRequestItem::STATUS_RETURNED,
                                        JobRequestItem::STATUS_REOPENED,
                                    ]);
                            });
                    }),
                'items as due_today_items_count' => fn ($query) => $query
                    ->whereDate('due_date', today())
                    ->whereIn('status', [
                        JobRequestItem::STATUS_OPEN,
                        JobRequestItem::STATUS_CLAIMED,
                        JobRequestItem::STATUS_RETURNED,
                        JobRequestItem::STATUS_REOPENED,
                    ]),
            ])
            ->latest()
            ->paginate(20);

        return view('admin.job-requests.index', compact('jobRequests'));
    }

    public function create()
    {
        $clients = Client::query()
            ->orderBy('client_name')
            ->get();

        $serviceCategories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $fieldStaff = User::query()
            ->where('role', 'field_staff')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('admin.job-requests.create', compact('clients', 'serviceCategories', 'fieldStaff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'assigned_field_staff_id' => [
                    'nullable',
                    Rule::exists('users', 'id')->where(fn ($query) => $query
                        ->where('role', 'field_staff')
                        ->where('status', 'approved')),
                ],
                'categories' => 'required|array|min:1',
                'categories.*' => 'integer|exists:service_categories,id',
            ],
            [
                'categories.required' => 'Please select at least one service category.',
                'client_id.required' => 'Please select a client.',
            ]
        );

        $categoryIds = collect($validated['categories'])
            ->map(fn ($categoryId) => (int) $categoryId)
            ->unique()
            ->values();

        $jobRequest = DB::transaction(function () use ($validated, $categoryIds, $request) {
            $jobRequest = JobRequest::create([
                'client_id' => $validated['client_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
                'status' => 'open',
            ]);

            $categories = ServiceCategory::query()
                ->whereIn('id', $categoryIds)
                ->get(['id', 'name']);

            foreach ($categories as $category) {
                $assignedFieldStaffId = $validated['assigned_field_staff_id'] ?? null;

                JobRequestItem::create([
                    'job_request_id' => $jobRequest->id,
                    'service_category_id' => $category->id,
                    'created_by' => $request->user()->id,
                    'claimed_by' => $assignedFieldStaffId,
                    'claimed_at' => $assignedFieldStaffId ? now() : null,
                    'status' => $assignedFieldStaffId
                        ? JobRequestItem::STATUS_CLAIMED
                        : JobRequestItem::STATUS_OPEN,
                    'title' => $category->name,
                    'due_date' => $validated['due_date'] ?? null,
                ]);
            }

            return $jobRequest;
        });

        return redirect()
            ->route('admin.job-requests.show', $jobRequest)
            ->with('success', 'Job request created successfully.');
    }

    public function show(JobRequest $jobRequest)
    {
        $jobRequest->load([
            'client',
            'creator',
            'items' => fn ($query) => $query->with(['serviceCategory', 'claimer', 'project'])->orderBy('id'),
        ]);

        return view('admin.job-requests.show', compact('jobRequest'));
    }
}
