@extends('layouts.field')

@section('title', 'My Tasks | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">My Tasks</span>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">Tasks List</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review assigned work items and update status from the field.</p>
    </div>

    <!-- Tasks List -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Assigned Tasks</h2>
        
        @if($tasks->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-550 font-semibold shadow-xs">
                No tasks assigned to you yet.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($tasks as $task)
                    @php
                        $linked = $task->assignable;
                        $isInspection = $task->assignable_type === \App\Models\Inspection::class;
                        $isProject = $task->assignable_type === \App\Models\Project::class;
                        $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
                        $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
                        $status = strtolower($task->status ?? 'pending');
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-slate-500 font-bold">
                                    {{ $linkedType }}: {{ $linkedCode ?? 'N/A' }}
                                </span>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $task->title }}</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium mt-0.5">Priority: {{ ucfirst($task->priority ?? 'medium') }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase whitespace-nowrap
                                {{ $status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-955/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : 'bg-amber-50 dark:bg-amber-955/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50' }}">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 dark:border-slate-850 pt-2.5">
                            <span class="text-slate-400 dark:text-slate-500">Due: <strong class="text-slate-700 dark:text-slate-300">{{ $task->due_date?->format('d M Y H:i') ?? '-' }}</strong></span>
                            <a href="{{ route('field.tasks.show', $task) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-extrabold">
                                Open &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($tasks->hasPages())
                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
