@extends('admin.layout')

@section('title', 'Job Requests | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Job Requests</h1>
                <p class="text-sm text-gray-600 mt-1">Create client requests and organize work by service category.</p>
            </div>
            <a href="{{ route('admin.job-requests.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                Create Job Request
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if($jobRequests->count() === 0)
                <div class="p-10 text-center text-gray-600">
                    No job requests yet. Create your first job.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category Items</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Due / Overdue</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Created Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($jobRequests as $jobRequest)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900 max-w-[300px] truncate">{{ $jobRequest->title }}</div>
                                        @if($jobRequest->description)
                                            <div class="text-xs text-gray-500 max-w-[300px] truncate">{{ $jobRequest->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $jobRequest->client?->client_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $jobRequest->items_count }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        <div>Due today: {{ $jobRequest->due_today_items_count }}</div>
                                        <div class="{{ $jobRequest->overdue_items_count > 0 ? 'font-semibold text-red-700' : 'text-gray-500' }}">Overdue: {{ $jobRequest->overdue_items_count }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ in_array($jobRequest->status, ['open', 'reopened'], true) ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-700' }}">
                                            {{ str_replace('_', ' ', \Illuminate\Support\Str::title($jobRequest->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $jobRequest->created_at?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end">
                                            <a href="{{ route('admin.job-requests.show', $jobRequest) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $jobRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
