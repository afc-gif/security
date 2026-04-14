@extends('admin.layout')

@section('title', 'Field Reports | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Field Reports</h1>
                <p class="text-sm text-gray-600 mt-1">Monitor inspection submissions, project updates, and task completion from one place.</p>
            </div>
        </div>

        <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
            <form method="GET" action="{{ route('admin.field-reports.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-1">Start Date</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ $filters['start_date'] }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-1">End Date</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ $filters['end_date'] }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label for="quick_range" class="block text-sm font-semibold text-gray-700 mb-1">Quick Date</label>
                        <select id="quick_range" name="quick_range" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Custom or all dates</option>
                            <option value="today" @selected($filters['quick_range'] === 'today')>Today</option>
                            <option value="this_week" @selected($filters['quick_range'] === 'this_week')>This Week</option>
                            <option value="this_month" @selected($filters['quick_range'] === 'this_month')>This Month</option>
                        </select>
                    </div>
                    <div>
                        <label for="field_staff_id" class="block text-sm font-semibold text-gray-700 mb-1">Field Staff</label>
                        <select id="field_staff_id" name="field_staff_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All field staff</option>
                            @foreach($fieldStaff as $staff)
                                <option value="{{ $staff->id }}" @selected((string) $filters['field_staff_id'] === (string) $staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-semibold text-gray-700 mb-1">Report Type</label>
                        <select id="type" name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" @selected($filters['type'] === 'all')>All activity</option>
                            <option value="inspections" @selected($filters['type'] === 'inspections')>Inspections</option>
                            <option value="project_updates" @selected($filters['type'] === 'project_updates')>Project Updates</option>
                            <option value="tasks" @selected($filters['type'] === 'tasks')>Tasks</option>
                        </select>
                    </div>
                    <div>
                        <label for="review_status" class="block text-sm font-semibold text-gray-700 mb-1">Review Status</label>
                        <select id="review_status" name="review_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" @selected($filters['review_status'] === 'all')>All review statuses</option>
                            <option value="pending_review" @selected($filters['review_status'] === 'pending_review')>Pending Review</option>
                            <option value="approved" @selected($filters['review_status'] === 'approved')>Approved</option>
                            <option value="rejected" @selected($filters['review_status'] === 'rejected')>Rejected</option>
                            <option value="reviewed" @selected($filters['review_status'] === 'reviewed')>Reviewed</option>
                            <option value="needs_correction" @selected($filters['review_status'] === 'needs_correction')>Needs Correction</option>
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search</label>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Inspection code/title, project code/title, or task title"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.field-reports.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200">
                            Reset
                        </a>
                    </div>

                    @if(count($activeFilters) > 0)
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                            <span class="font-semibold text-gray-700">Showing results for:</span>
                            @foreach($activeFilters as $filter)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $filter['label'] }}: {{ $filter['value'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </form>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm font-semibold text-gray-500">Pending inspection reviews</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $pendingInspectionReviewsCount }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm font-semibold text-gray-500">Recent project updates</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $recentProjectUpdatesCount }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm font-semibold text-gray-500">Updates needing correction</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $projectUpdatesNeedingCorrectionCount }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm font-semibold text-gray-500">Completed tasks</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $completedTasksCount }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Pending Inspection Reviews</h2>
                </div>
                @if($pendingInspectionReviews->count() === 0)
                    <div class="p-6 text-gray-600">No inspection reports are waiting for review.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Inspection</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Field Staff</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Submitted</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($pendingInspectionReviews as $inspection)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-900 whitespace-nowrap">{{ $inspection->inspection_code }}</div>
                                            <div class="text-xs text-gray-500 max-w-[240px] truncate">{{ $inspection->title }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $inspection->client?->client_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $inspection->assignedUser?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $inspection->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.inspections.show', $inspection) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Recent Project Updates</h2>
                </div>
                @if($recentProjectUpdates->count() === 0)
                    <div class="p-6 text-gray-600">No project updates submitted yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Project</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Submitted By</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Review</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Progress</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentProjectUpdates as $update)
                                    @php($reviewStatus = $update->review_status ?? 'pending_review')
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-900 whitespace-nowrap">{{ $update->project?->project_code ?? '—' }}</div>
                                            <div class="text-xs text-gray-500 max-w-[240px] truncate">{{ $update->project?->title ?? 'Project unavailable' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $update->user?->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $reviewStatus === 'reviewed' ? 'bg-green-100 text-green-800' : ($reviewStatus === 'needs_correction' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($reviewStatus)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $update->progress_percentage !== null ? $update->progress_percentage . '%' : '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if($update->project)
                                                <a href="{{ route('admin.projects.show', $update->project) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                    View
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-500">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Updates Needing Correction</h2>
                </div>
                @if($projectUpdatesNeedingCorrection->count() === 0)
                    <div class="p-6 text-gray-600">No project updates need correction.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($projectUpdatesNeedingCorrection as $update)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $update->project?->project_code ?? 'Project unavailable' }}</div>
                                    <div class="text-sm text-gray-600">{{ $update->summary ?: $update->project?->title ?? 'Project update' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Submitted by {{ $update->user?->name ?? '—' }} on {{ $update->created_at?->format('d M Y H:i') ?? '—' }}</div>
                                </div>
                                @if($update->project)
                                    <a href="{{ route('admin.projects.show', $update->project) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm font-semibold">
                                        Open Project
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Recently Completed Tasks</h2>
                </div>
                @if($recentlyCompletedTasks->count() === 0)
                    <div class="p-6 text-gray-600">No completed tasks yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Task</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Linked To</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Field Staff</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Completed</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentlyCompletedTasks as $task)
                                    @php
                                        $linked = $task->assignable;
                                        $linkedType = match ($task->assignable_type) {
                                            \App\Models\Inspection::class => 'Inspection',
                                            \App\Models\Project::class => 'Project',
                                            default => 'Linked record',
                                        };
                                        $linkedCode = match ($task->assignable_type) {
                                            \App\Models\Inspection::class => $linked?->inspection_code,
                                            \App\Models\Project::class => $linked?->project_code,
                                            default => null,
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $task->title }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">
                                            <div>{{ $linkedType }}</div>
                                            <div class="text-xs text-gray-500">{{ $linkedCode ?? 'Linked record unavailable' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $task->assignee?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $task->completed_at?->format('d M Y H:i') ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.tasks.show', $task) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Recent Field Activity</h2>
            </div>
            @if($activityFeed->count() === 0)
                <div class="p-6 text-gray-600">No recent field activity yet.</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($activityFeed as $item)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">{{ $item['type'] }}</span>
                                <div class="font-semibold text-gray-900 mt-2">{{ $item['title'] }}</div>
                                <div class="text-sm text-gray-600">By {{ $item['user'] }} · {{ $item['timestamp']?->format('d M Y H:i') ?? '—' }}</div>
                            </div>
                            @if($item['link'])
                                <a href="{{ $item['link'] }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                    Open
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
