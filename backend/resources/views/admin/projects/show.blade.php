@extends('admin.layout')

@section('title', 'Project Details | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->project_code }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $project->title }}</p>
            </div>
            <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Projects
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
                    <div class="text-gray-900 font-semibold">{{ $project->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $project->status === 'completed' ? 'bg-green-100 text-green-800' : ($project->status === 'ongoing' ? 'bg-blue-100 text-blue-800' : ($project->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 text-gray-700')) }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Location</div>
                    <div class="text-gray-900">{{ $project->location ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Priority</div>
                    <div class="text-gray-900">{{ $project->priority ? ucfirst($project->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Start Date</div>
                    <div class="text-gray-900">{{ $project->start_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Deadline</div>
                    <div class="text-gray-900">{{ $project->deadline?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned Manager</div>
                    <div class="text-gray-900">{{ $project->manager?->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $project->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Description</h2>
            <div class="text-gray-900 whitespace-pre-line">{{ $project->description ?: '—' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Linked Inspection</h2>
            @if($project->inspection)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $project->inspection->inspection_code }}</div>
                        <div class="text-sm text-gray-600">{{ $project->inspection->title }}</div>
                    </div>
                    <a href="{{ route('admin.inspections.show', $project->inspection) }}" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                        View Inspection
                    </a>
                </div>
            @else
                <div class="text-gray-600">This project was created manually and is not linked to an inspection.</div>
            @endif
        </div>
    </div>
</div>
@endsection
