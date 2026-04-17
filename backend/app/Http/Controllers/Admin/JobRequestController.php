<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
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

        return view('admin.job-requests.create', compact('clients', 'serviceCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
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
                JobRequestItem::create([
                    'job_request_id' => $jobRequest->id,
                    'service_category_id' => $category->id,
                    'created_by' => $request->user()->id,
                    'status' => 'open',
                    'title' => $category->name,
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
            'items' => fn ($query) => $query->with(['serviceCategory', 'claimer'])->orderBy('id'),
        ]);

        return view('admin.job-requests.show', compact('jobRequest'));
    }
}
