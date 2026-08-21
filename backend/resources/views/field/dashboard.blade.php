@extends('layouts.field')

@php
    $fieldUser = auth()->user();
    $isCoordinator = $fieldUser?->isFieldCoordinator() ?? false;
    $formatStatus = fn ($status) => str_replace('_', ' ', \Illuminate\Support\Str::title($status ?? 'Unknown'));
    $statusClass = fn ($status) => str_replace('_', '-', strtolower((string) ($status ?? 'unknown')));
    $isUrgentJob = function ($job): bool {
        $status = strtolower((string) ($job->status ?? ''));
        return in_array($status, ['returned', 'overdue'], true)
            || ($job->due_date && now()->greaterThan($job->due_date) && in_array($status, ['claimed', 'submitted'], true));
    };
    $priorityJobs = $recentJobs->filter($isUrgentJob)->take(3);
@endphp

@section('title', 'Field Dashboard | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Welcome section -->
    <div class="bg-gradient-to-tr from-indigo-600 to-violet-600 dark:from-indigo-750 dark:to-violet-750 rounded-2xl p-5 text-white shadow-md">
        <span class="text-xs font-bold tracking-wider opacity-85 uppercase">Welcome Back</span>
        <h1 class="text-2xl font-extrabold mt-0.5">Hi, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <p class="text-xs mt-1.5 opacity-90 leading-relaxed">Start with urgent returned jobs or claimed actions, then complete your assigned task checklists.</p>
    </div>

    <!-- Coordinator Alert Section -->
    @if($isCoordinator)
        <div id="coordinator-alert" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm transition-all {{ $pendingAssignmentCount > 0 ? 'border-amber-200 dark:border-amber-900 bg-amber-50/50 dark:bg-amber-950/20' : 'hidden' }}"
             data-pending-assignment-url="{{ route('field.dashboard.pending-assignments') }}"
             data-initial-pending-count="{{ $pendingAssignmentCount }}">
             <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <h3 id="coordinator-alert-title" class="text-xs font-extrabold text-amber-800 dark:text-amber-400 uppercase tracking-wider">
                        {{ $pendingAssignmentCount }} {{ \Illuminate\Support\Str::plural('job', $pendingAssignmentCount) }} waiting for assignment
                    </h3>
                    <p id="coordinator-alert-message" class="text-xs text-amber-700 dark:text-amber-500 leading-relaxed">
                        New job requests require a field coordinator to assign them to field staff.
                    </p>
                </div>
                <span id="coordinator-alert-count" class="h-6 w-6 rounded-full bg-amber-200 dark:bg-amber-900/50 text-amber-900 dark:text-amber-300 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ $pendingAssignmentCount }}
                </span>
            </div>
            
            @if($pendingAssignmentJobs->isNotEmpty())
                <div class="mt-3.5 space-y-2 border-t border-amber-200/40 dark:border-amber-800/40 pt-3">
                    @foreach($pendingAssignmentJobs as $job)
                        <div class="bg-white/80 dark:bg-slate-950/80 p-2.5 rounded-xl border border-amber-200/20 dark:border-amber-850/20 text-xs">
                            <span class="font-bold text-slate-800 dark:text-white block">{{ $job->jobRequest?->title ?? $job->title ?? 'Job Request' }}</span>
                            <span class="text-slate-500 dark:text-slate-400 mt-0.5 block">{{ $job->jobRequest?->client?->client_name ?? 'Client' }} · {{ $job->serviceCategory?->name ?? 'Service category' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('coordinator.jobs.index') }}" class="w-full inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 px-4 rounded-xl transition-all shadow-sm">
                    Go to Assignment Panel
                </a>
            </div>
        </div>
    @endif

    <!-- Summary Stats Row -->
    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('field.jobs.index') }}" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-3.5 rounded-2xl text-center shadow-xs hover:border-indigo-100 dark:hover:border-indigo-900 transition-all">
            <span class="block text-xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ $availableJobsCount }}</span>
            <span class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">Available</span>
        </a>
        <a href="{{ route('field.jobs.index') }}" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-3.5 rounded-2xl text-center shadow-xs hover:border-indigo-100 dark:hover:border-indigo-900 transition-all">
            <span class="block text-xl font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums">{{ $myJobsCount }}</span>
            <span class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">My Jobs</span>
        </a>
        <a href="{{ route('field.jobs.index') }}" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-3.5 rounded-2xl text-center shadow-xs hover:border-indigo-100 dark:hover:border-indigo-900 transition-all">
            <span class="block text-xl font-extrabold text-rose-600 dark:text-rose-450 tabular-nums">{{ $overdueJobsCount }}</span>
            <span class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">Overdue</span>
        </a>
    </div>

    <!-- Priority Alerts Section -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-950 dark:text-white uppercase tracking-wider">Priority Attention</h2>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">Returned &amp; Overdue</span>
        </div>

        @if($priorityJobs->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-505 font-semibold shadow-xs">
                No returned or overdue jobs requiring priority attention.
            </div>
        @else
            <div class="space-y-3">
                @foreach($priorityJobs as $job)
                    @php
                        $isReturned = strtolower($job->status) === 'returned';
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $job->jobRequest?->title ?? 'Job Request' }}</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">{{ $job->jobRequest?->client?->client_name ?? 'Client name' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase whitespace-nowrap
                                {{ $isReturned ? 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-450 border border-amber-100 dark:border-amber-900/50' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-450 border border-rose-100 dark:border-rose-900/50' }}">
                                {{ $isReturned ? 'Returned' : 'Overdue' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 dark:border-slate-850 pt-2.5">
                            <span class="text-slate-400 dark:text-slate-500">Due: <strong class="text-slate-700 dark:text-slate-300">{{ $job->due_date?->format('d M Y H:i') ?? '-' }}</strong></span>
                            <a href="{{ route('field.jobs.show', $job) }}" class="text-xs text-indigo-650 dark:text-indigo-400 hover:underline font-bold">Fix Report &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Active Projects List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-950 dark:text-white uppercase tracking-wider">Active Projects</h2>
            <a href="{{ route('field.projects.index') }}" class="text-[10px] text-indigo-650 dark:text-indigo-400 font-extrabold uppercase">View All</a>
        </div>

        @if($recentProjects->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-500 font-semibold shadow-xs">
                No active projects assigned to you.
            </div>
        @else
            <div class="space-y-3">
                @foreach($recentProjects as $project)
                    @php
                        $isLocked = $project->isBeingEdited();
                        $progress = min(100, max(0, (int) ($project->progress_percentage ?? 0)));
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-slate-500">{{ $project->project_code }}</span>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $project->title }}</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">Client: {{ $project->client?->client_name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-sky-50 dark:bg-sky-950/20 text-sky-700 dark:text-sky-400 border border-sky-100 dark:border-sky-900/50 whitespace-nowrap">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500">
                                <span>Progress</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full" @style(['width: ' . $progress . '%'])></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 dark:border-slate-850 pt-2.5">
                            <span class="text-[9px] max-w-[200px] truncate text-slate-400 dark:text-slate-500">
                                @if($isLocked)
                                    Locked by {{ explode(' ', $project->activeEditor?->name)[0] }}
                                @else
                                    Ready for updates.
                                @endif
                            </span>
                            <a href="{{ route('field.projects.show', $project) }}" class="text-xs text-indigo-650 dark:text-indigo-400 hover:underline font-bold">Open &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if($isCoordinator)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const alert = document.getElementById('coordinator-alert');
            if (!alert) return;

            const countEl = document.getElementById('coordinator-alert-count');
            const titleEl = document.getElementById('coordinator-alert-title');
            const messageEl = document.getElementById('coordinator-alert-message');
            const endpoint = alert.dataset.pendingAssignmentUrl;
            let lastCount = Number(alert.dataset.initialPendingCount || 0);

            const updateAlert = (count) => {
                const label = count === 1 ? 'job' : 'jobs';
                if (count < 1) {
                    alert.classList.add('hidden');
                } else {
                    alert.classList.remove('hidden');
                    countEl.textContent = String(count);
                    titleEl.textContent = `${count} ${label} waiting for assignment`;
                    messageEl.textContent = count > lastCount
                        ? 'New job request received. Please assign it to field staff.'
                        : 'New job requests are waiting for a field coordinator to assign them.';
                }
                lastCount = count;
            };

            const refreshPendingAssignments = async () => {
                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (response.ok) {
                        const data = await response.json();
                        updateAlert(Number(data.count || 0));
                    }
                } catch (error) {
                    console.error('Failed refreshing pending assignments:', error);
                }
            };

            setInterval(refreshPendingAssignments, 30000);
        });
    </script>
@endif
@endsection
