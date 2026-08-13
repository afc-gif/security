<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\JobChecklistItem;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->withCount(['checklistTemplates as active_checklist_templates_count' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.job-requests.create', compact('clients', 'serviceCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'categories' => 'required|array|min:1',
                'categories.*' => 'integer|exists:service_categories,id',
                'additional_checklist' => 'nullable|string',
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
        $additionalChecklistItems = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['additional_checklist'] ?? '')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values();

        $jobRequest = DB::transaction(function () use ($validated, $categoryIds, $additionalChecklistItems, $request) {
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
                $jobItem = JobRequestItem::create([
                    'job_request_id' => $jobRequest->id,
                    'service_category_id' => $category->id,
                    'created_by' => $request->user()->id,
                    'claimed_by' => null,
                    'claimed_at' => null,
                    'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
                    'title' => $category->name,
                    'due_date' => $validated['due_date'] ?? null,
                ]);

                $jobItem->ensureChecklistFromCategory();

                foreach ($additionalChecklistItems as $index => $checklistTitle) {
                    $jobItem->checklistItems()->create([
                        'added_by' => $request->user()->id,
                        'title' => $checklistTitle,
                        'status' => 'pending',
                        'is_required' => false,
                        'is_custom' => true,
                        'sort_order' => 1000 + $index,
                    ]);
                }
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
            'items' => fn ($query) => $query->with(['serviceCategory', 'claimer', 'project', 'checklistItems'])->orderBy('id'),
        ]);

        return view('admin.job-requests.show', compact('jobRequest'));
    }

    public function update(Request $request, JobRequest $jobRequest)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $jobRequest->update([
            'title' => trim($validated['title']),
        ]);

        return redirect()
            ->route('admin.job-requests.show', $jobRequest)
            ->with('success', 'Job title updated.');
    }

    public function destroy(JobRequest $jobRequest)
    {
        if ($jobRequest->items()->whereHas('project')->exists()) {
            return redirect()
                ->route('admin.job-requests.show', $jobRequest)
                ->withErrors(['job' => 'This job cannot be deleted because it has already been converted to a project.']);
        }

        $jobRequest->delete();

        return redirect()
            ->route('admin.job-requests.index')
            ->with('success', 'Job deleted.');
    }

    public function destroyChecklistItem(JobRequestItem $jobItem, JobChecklistItem $checklistItem)
    {
        abort_unless((int) $checklistItem->job_request_item_id === (int) $jobItem->id, 404);

        if (!in_array($jobItem->status, [
            JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            JobRequestItem::STATUS_OPEN,
            JobRequestItem::STATUS_CLAIMED,
            JobRequestItem::STATUS_RETURNED,
        ], true)) {
            return back()->withErrors(['checklist' => 'Checklist cannot be changed for this job status.']);
        }

        $checklistItem->delete();

        return redirect()
            ->route('admin.job-requests.show', $jobItem->job_request_id)
            ->with('success', 'Checklist item removed.');
    }

    public function addChecklistItem(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if (!in_array($jobItem->status, [
            JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            JobRequestItem::STATUS_OPEN,
            JobRequestItem::STATUS_CLAIMED,
            JobRequestItem::STATUS_RETURNED,
        ], true)) {
            return back()->withErrors(['checklist' => 'Checklist cannot be changed for this job status.']);
        }

        $jobItem->ensureChecklistFromCategory();
        $jobItem->checklistItems()->create([
            'added_by' => $request->user()->id,
            'title' => trim($validated['title']),
            'description' => isset($validated['description']) && trim((string) $validated['description']) !== ''
                ? trim((string) $validated['description'])
                : null,
            'status' => 'pending',
            'is_required' => false,
            'is_custom' => true,
            'sort_order' => ((int) $jobItem->checklistItems()->max('sort_order')) + 1,
        ]);

        return redirect()
            ->route('admin.job-requests.show', $jobItem->job_request_id)
            ->with('success', 'Checklist item added.');
    }
}
