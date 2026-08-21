@extends('layouts.field')

@section('title', 'Projects Log | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Projects Log</span>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">Projects</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">All field staff can view projects. One person can write updates at a time.</p>
    </div>

    <!-- Projects List -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Assigned Projects</h2>
        
        @if($projects->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-500 font-semibold shadow-xs">
                No projects assigned to you yet.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($projects as $project)
                    @php
                        $isLocked = $project->isBeingEdited();
                        $lockExpired = $project->editingLockExpired();
                        $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
                        $isCompleted = $project->status === 'completed';
                        $isReadyForReview = $project->status === 'ready_for_review';
                        $progress = min(100, max(0, (int) ($project->progress_percentage ?? 0)));
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-slate-500 font-bold">
                                    {{ $project->project_code }}
                                </span>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $project->title }}</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">Client: {{ $project->client?->client_name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase whitespace-nowrap
                                {{ $isCompleted ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : 'bg-sky-50 dark:bg-sky-950/20 text-sky-700 dark:text-sky-400 border border-sky-100 dark:border-sky-900/50' }}">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500">
                                <span>Project Progress</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full" @style(['width: ' . $progress . '%'])></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 dark:border-slate-850 pt-2.5">
                            <span class="text-[9px] max-w-[200px] truncate text-slate-400 dark:text-slate-505">
                                @if($isCompleted)
                                    Project completed.
                                @elseif($isReadyForReview)
                                    Waiting for review.
                                @elseif($lockExpired)
                                    Previous edit lock expired.
                                @elseif($lockedByMe)
                                    <strong class="text-emerald-600 dark:text-emerald-450">Locked by you.</strong>
                                @elseif($isLocked)
                                    Locked by {{ explode(' ', $project->activeEditor?->name)[0] }}.
                                @else
                                    Ready to update.
                                @endif
                            </span>
                            <a href="{{ route('field.projects.show', $project) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-extrabold">
                                Open Project &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($projects->hasPages())
                <div class="mt-4">
                    {{ $projects->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
