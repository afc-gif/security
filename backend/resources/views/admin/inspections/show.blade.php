@extends('admin.layout')

@section('title', 'Inspection Details | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $inspection->inspection_code }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $inspection->title }}</p>
            </div>
            <a href="{{ route('admin.inspections.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Inspections
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
                    <div class="text-gray-900 font-semibold">{{ $inspection->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned To</div>
                    <div class="text-gray-900 font-semibold">{{ $inspection->assignedUser?->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Location</div>
                    <div class="text-gray-900">{{ $inspection->location }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Inspection Type</div>
                    <div class="text-gray-900">{{ $inspection->inspection_type ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Scheduled Date</div>
                    <div class="text-gray-900">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Priority</div>
                    <div class="text-gray-900">{{ $inspection->priority ? ucfirst($inspection->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $inspection->status === 'completed' ? 'bg-green-100 text-green-800' : ($inspection->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($inspection->status) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $inspection->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
