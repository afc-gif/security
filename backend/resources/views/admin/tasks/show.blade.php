@extends('admin.layout')

@section('title', 'Task Details | ARTSCI Admin Console')

@section('content')
@php
    $linked = $task->assignable;
    $isInspection = $task->assignable_type === \App\Models\Inspection::class;
    $isProject = $task->assignable_type === \App\Models\Project::class;
    $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
    $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
    $linkedTitle = $linked?->title;
    $linkedRoute = $linked ? ($isInspection ? route('admin.inspections.show', $linked) : ($isProject ? route('admin.projects.show', $linked) : null)) : null;
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $task->title }}</h1>
                <p class="text-sm text-gray-600 mt-1">Task details</p>
            </div>
            <a href="{{ route('admin.tasks.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Tasks
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
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : ($task->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Priority</div>
                    <div class="text-gray-900">{{ $task->priority ? ucfirst($task->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned To</div>
                    <div class="text-gray-900 font-semibold">{{ $task->assignee?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned By</div>
                    <div class="text-gray-900">{{ $task->assigner?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Due Date</div>
                    <div class="text-gray-900">{{ $task->due_date?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Completed At</div>
                    <div class="text-gray-900">{{ $task->completed_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Linked {{ $linkedType }}</h2>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div>
                    <div class="font-semibold text-gray-900">{{ $linkedCode ?? 'Linked record unavailable' }}</div>
                    <div class="text-sm text-gray-600">{{ $linkedTitle ?? 'The linked inspection or project could not be found.' }}</div>
                </div>
                @if($linkedRoute)
                    <a href="{{ $linkedRoute }}" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                        View {{ $linkedType }}
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
            <div class="text-gray-900 whitespace-pre-line">{{ $task->description ?: '—' }}</div>
        </div>
    </div>
</div>
@endsection
