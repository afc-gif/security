@extends('admin.layout')

@section('title', 'Job Inbox | ARTSCI Admin Console')

@section('content')
@php
    $statusClass = fn (?string $status) => match ($status) {
        'pending_assignment' => 'bg-purple-100 text-purple-800',
        'open', 'reopened', 'claimed' => 'bg-blue-100 text-blue-800',
        'submitted', 'pending_admin_review' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'returned' => 'bg-orange-100 text-orange-800',
        'rejected', 'overdue' => 'bg-red-100 text-red-800',
        'closed' => 'bg-gray-300 text-gray-800',
        default => 'bg-gray-200 text-gray-700',
    };

    $sectionMeta = [
        'pendingReview' => ['title' => 'Pending Admin Review', 'empty' => 'No pending admin review items.'],
        'overdue' => ['title' => 'Overdue', 'empty' => 'No overdue items.'],
        'returnedReopened' => ['title' => 'Returned / Reopened', 'empty' => 'No returned or reopened items.'],
        'approved' => ['title' => 'Approved', 'empty' => 'No approved items.'],
        'converted' => ['title' => 'Converted', 'empty' => 'No converted items.'],
        'recentlyActive' => ['title' => 'Recently Active', 'empty' => 'No recent activity.'],
    ];
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Job Inbox</h1>
                <p class="text-sm text-gray-600 mt-1">Monitor category items by client, job request, review status, deadline, and project conversion.</p>
            </div>
            <a href="{{ route('admin.job-requests.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                Create Job Request
            </a>
        </div>

        <form method="GET" action="{{ route('admin.job-inbox.index') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                    <select name="client_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) $filters['client_id'] === (string) $client->id)>{{ $client->client_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Conversion</label>
                    <select name="converted" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="all" @selected($filters['converted'] === 'all')>All</option>
                        <option value="converted" @selected($filters['converted'] === 'converted')>Converted</option>
                        <option value="not_converted" @selected($filters['converted'] === 'not_converted')>Not converted</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Client, job, category">
                </div>
                <div class="flex flex-col justify-end gap-2">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="due_today" value="1" @checked($filters['due_today']) class="rounded border-gray-300 text-blue-600">
                        Due today
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Filter</button>
                        <a href="{{ route('admin.job-inbox.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold text-center">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm text-gray-600">Pending Admin Review</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['pending_review'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm text-gray-600">Overdue</div>
                <div class="text-3xl font-bold text-red-700 mt-2">{{ $summary['overdue'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm text-gray-600">Returned / Reopened</div>
                <div class="text-3xl font-bold text-orange-700 mt-2">{{ $summary['returned_reopened'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="text-sm text-gray-600">Converted</div>
                <div class="text-3xl font-bold text-green-700 mt-2">{{ $summary['converted'] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            @foreach($sectionMeta as $sectionKey => $meta)
                <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-xl font-bold text-gray-900">{{ $meta['title'] }}</h2>
                        <span class="text-sm text-gray-500">{{ $sections[$sectionKey]->count() }}</span>
                    </div>

                    @if($sections[$sectionKey]->count() === 0)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-gray-600">{{ $meta['empty'] }}</div>
                    @else
                        <div class="space-y-3">
                            @foreach($sections[$sectionKey] as $item)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $item->serviceCategory?->name ?? $item->title ?? 'Category item' }}</div>
                                            <div class="text-sm text-gray-600">{{ $item->jobRequest?->client?->client_name ?? 'Client unavailable' }} · {{ $item->jobRequest?->title ?? 'Job request unavailable' }}</div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $statusClass($item->status) }}">
                                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($item->status)) }}
                                            </span>
                                            @if($item->project)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Converted</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-3 text-sm">
                                        <a href="{{ route('admin.job-items.show', $item) }}" class="font-semibold text-blue-700 hover:text-blue-900">Open Item</a>
                                        @if($item->jobRequest)
                                            <a href="{{ route('admin.job-requests.show', $item->jobRequest) }}" class="font-semibold text-blue-700 hover:text-blue-900">Open Job Request</a>
                                        @endif
                                        @if($item->project)
                                            <a href="{{ route('admin.projects.show', $item->project) }}" class="font-semibold text-green-700 hover:text-green-900">Open Project</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <h2 class="text-xl font-bold text-gray-900">Grouped by Client and Job Request</h2>
                <div class="text-sm text-gray-500">Showing up to 100 matching items</div>
            </div>

            @if($groupedItems->count() === 0)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center text-gray-600">
                    No matching job items found for these filters.
                </div>
            @else
                <div class="space-y-5">
                    @foreach($groupedItems as $clientName => $jobRequests)
                        <div class="rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-100 px-4 py-3 font-bold text-gray-900">{{ $clientName }}</div>
                            <div class="divide-y divide-gray-100">
                                @foreach($jobRequests as $jobRequestTitle => $items)
                                    @php($firstItem = $items->first())
                                    <div class="p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                            <div class="font-semibold text-gray-900">{{ $jobRequestTitle }}</div>
                                            @if($firstItem?->jobRequest)
                                                <a href="{{ route('admin.job-requests.show', $firstItem->jobRequest) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Open Job Request</a>
                                            @endif
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($items as $item)
                                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                                    <div>
                                                        <div class="font-semibold text-gray-900">{{ $item->serviceCategory?->name ?? $item->title ?? 'Category item' }}</div>
                                                        <div class="text-sm text-gray-600">
                                                            Claimed by {{ $item->claimer?->name ?? '—' }}
                                                            · Due {{ $item->due_date?->format('d M Y H:i') ?? '—' }}
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                                        <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold {{ $statusClass($item->status) }}">
                                                            {{ str_replace('_', ' ', \Illuminate\Support\Str::title($item->status)) }}
                                                        </span>
                                                        @if($item->project)
                                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Converted</span>
                                                        @else
                                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-700">Not converted</span>
                                                        @endif
                                                        <a href="{{ route('admin.job-items.show', $item) }}" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-md font-semibold text-sm">Open Item</a>
                                                        @if(in_array($item->status, ['overdue', 'closed', 'rejected'], true))
                                                            <form method="POST" action="{{ route('admin.job-items.reopen', $item) }}" class="inline-block" onsubmit="return confirm('Reopen this overdue job?');">
                                                                @csrf
                                                                <button type="submit" class="inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-md font-semibold text-sm">Reopen</button>
                                                            </form>
                                                        @endif
                                                        @if($item->project)
                                                            <a href="{{ route('admin.projects.show', $item->project) }}" class="inline-flex items-center justify-center bg-green-50 text-green-700 hover:bg-green-100 px-3 py-1.5 rounded-md font-semibold text-sm">Open Project</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
