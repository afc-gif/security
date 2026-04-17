@extends('admin.layout')

@section('title', 'Job Request Details | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $jobRequest->title }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $jobRequest->client?->client_name ?? 'Client unavailable' }}</p>
            </div>
            <a href="{{ route('admin.job-requests.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Job Requests
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Client</div>
                    <div class="text-gray-900 font-semibold">{{ $jobRequest->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-700">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($jobRequest->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $jobRequest->creator?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created Date</div>
                    <div class="text-gray-900">{{ $jobRequest->created_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
            <div class="text-gray-900 whitespace-pre-line">{{ $jobRequest->description ?: '—' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <h2 class="text-xl font-bold text-gray-900">Category Items</h2>
                <div class="text-sm text-gray-600">{{ $jobRequest->items->count() }} item{{ $jobRequest->items->count() === 1 ? '' : 's' }}</div>
            </div>

            @if($jobRequest->items->count() === 0)
                <div class="text-gray-600">No category items created for this job.</div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($jobRequest->items as $item)
                        @php
                            $itemStatusClass = match ($item->status) {
                                'claimed' => 'bg-blue-100 text-blue-800',
                                'submitted' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-200 text-gray-700',
                            };
                        @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $item->serviceCategory?->name ?? $item->title ?? 'Service Category' }}</div>
                                    @if($item->title && $item->serviceCategory && $item->title !== $item->serviceCategory->name)
                                        <div class="text-sm text-gray-600 mt-1">{{ $item->title }}</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold whitespace-nowrap {{ $itemStatusClass }}">
                                    {{ str_replace('_', ' ', \Illuminate\Support\Str::title($item->status)) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Claimed By</div>
                                    <div class="text-gray-900">{{ $item->claimer?->name ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Due Date</div>
                                    <div class="text-gray-900">{{ $item->due_date?->format('d M Y H:i') ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
