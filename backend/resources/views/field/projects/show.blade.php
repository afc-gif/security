@extends('layouts.field')

@section('title', $project->project_code . ' | ARTSCI')

@section('content')
<div class="space-y-6">
    @php
        $isLocked = $project->isBeingEdited();
        $lockExpired = $project->editingLockExpired();
        $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
        $isCompleted = $project->status === 'completed' || (int) $project->progress_percentage === 100;
        $isReadyForReview = $project->status === 'ready_for_review' && (int) $project->progress_percentage !== 100;
        $latestProjectUpdate = $project->updates->first();
        $progress = min(100, max(0, (int) ($project->progress_percentage ?? 0)));
    @endphp

    <!-- Header banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <span class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">{{ $project->project_code }}</span>
            <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase
                {{ $isCompleted ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : 'bg-sky-50 dark:bg-sky-950/20 text-sky-700 dark:text-sky-400 border border-sky-100 dark:border-sky-900/50' }}">
                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($isCompleted ? 'completed' : $project->status)) }}
            </span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white mt-2">{{ $project->title }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Managed field requirements, progress milestones, and daily reports.</p>
    </div>

    <!-- Project details grid -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider">Client</span>
            <strong class="block text-slate-800 dark:text-slate-200 mt-0.5">{{ $project->client?->client_name ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-555 uppercase tracking-wider">Deadline</span>
            <strong class="block text-slate-800 dark:text-slate-200 mt-0.5">{{ $project->deadline?->format('d M Y') ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider">Priority</span>
            <strong class="block text-slate-800 dark:text-slate-200 mt-0.5">{{ $project->priority ? ucfirst($project->priority) : '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider">Location</span>
            <strong class="block text-slate-800 dark:text-slate-200 mt-0.5 truncate" title="{{ $project->location }}">{{ $project->location ?: '-' }}</strong>
        </div>
        <div class="col-span-2 space-y-1.5 border-t border-slate-50 dark:border-slate-850 pt-2.5">
            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500">
                <span>Progress Percentage</span>
                <span class="text-slate-800 dark:text-slate-200">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full" @style(['width: ' . $progress . '%'])></div>
            </div>
        </div>
    </div>

    <!-- Requirements list -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 dark:text-white uppercase tracking-wider border-b border-slate-50 dark:border-slate-850 pb-2.5">Project Checklist</h2>
        
        @if($project->requirements->count() === 0)
            <div class="text-xs text-slate-400 dark:text-slate-500 text-center py-4 font-semibold">
                No approved requirements are attached to this project.
            </div>
        @else
            <div class="space-y-3">
                @foreach($project->requirements as $requirement)
                    <div class="flex items-start gap-3 p-3 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-850 rounded-xl">
                        <form method="POST" action="{{ route('field.projects.requirements.update', [$project, $requirement]) }}" class="mt-0.5">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_done" value="0">
                            <input type="checkbox" name="is_done" value="1" @checked($requirement->is_done) onchange="this.form.submit()" @disabled($isCompleted || $isReadyForReview) class="rounded border-slate-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        </form>
                        <div class="min-w-0 flex-1 text-xs">
                            <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-slate-500">{{ $requirement->type }}</span>
                            <span class="block font-bold text-slate-800 dark:text-white mt-0.5">{{ $requirement->name }}</span>
                            @if($requirement->quantity)
                                <span class="text-[10px] text-slate-500 dark:text-slate-405 mt-0.5 block">Quantity: {{ $requirement->quantity }}</span>
                            @endif
                            @if($requirement->notes)
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block italic">"{{ $requirement->notes }}"</span>
                            @endif
                            @if($requirement->is_done)
                                <span class="text-[9px] text-emerald-600 dark:text-emerald-450 font-semibold block mt-1">✓ Done by {{ $requirement->completedBy?->name ?? 'Staff' }} on {{ $requirement->completed_at?->format('d M Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Project Update Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-955 dark:text-white uppercase tracking-wider border-b border-slate-50 dark:border-slate-850 pb-2.5">Project Update</h2>

        @if($isCompleted)
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-955/20 border border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400 text-xs font-semibold rounded-xl text-center">
                Project completed.
            </div>
        @elseif($isReadyForReview)
            <div class="p-3.5 bg-sky-50 dark:bg-sky-955/20 border border-sky-100 dark:border-sky-900/50 text-sky-800 dark:text-sky-400 text-xs font-semibold rounded-xl text-center">
                Project is waiting for coordinator or admin review.
            </div>
        @elseif($lockedByMe)
            <div class="p-3 bg-indigo-50 dark:bg-indigo-955/20 border border-indigo-100 dark:border-indigo-900/50 text-indigo-800 dark:text-indigo-400 text-xs rounded-xl">
                You have the update lock. Submit your report below or release the lock.
            </div>

            <form method="POST" action="{{ route('field.projects.release-update', $project) }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-805 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition-all">
                    Release Lock
                </button>
            </form>

            <form method="POST" action="{{ route('field.projects.submit-update', $project) }}" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                
                <div class="space-y-1">
                    <label for="summary" class="block text-[10px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Summary / Title *</label>
                    <input id="summary" type="text" name="summary" value="{{ old('summary') }}" required maxlength="255" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="e.g. Completed cabling setup">
                </div>

                <div class="space-y-1">
                    <label for="work_done" class="block text-[10px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Work Done *</label>
                    <textarea id="work_done" name="work_done" rows="4" required class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Details of progress made..."></textarea>
                </div>

                <div class="space-y-1">
                    <label for="materials_used" class="block text-[10px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Materials Used</label>
                    <textarea id="materials_used" name="materials_used" rows="2" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="List items consumed on-site..."></textarea>
                </div>

                <div class="space-y-1">
                    <label for="issues_encountered" class="block text-[10px] font-bold text-slate-700 dark:text-slate-355 uppercase tracking-wider">Issues Encountered</label>
                    <textarea id="issues_encountered" name="issues_encountered" rows="2" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Note any delays, faults, or errors..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="progress_percentage" class="block text-[9px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">New Progress % *</label>
                        <input id="progress_percentage" type="number" name="progress_percentage" value="{{ old('progress_percentage', $project->progress_percentage ?? 0) }}" min="{{ (int) ($project->progress_percentage ?? 0) }}" max="100" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" required>
                    </div>
                    <div class="space-y-1">
                        <label for="work_date" class="block text-[9px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Work Date *</label>
                        <input id="work_date" type="date" name="work_date" value="{{ old('work_date', now()->format('Y-m-d')) }}" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="next_step" class="block text-[10px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Next Step / Tasks</label>
                    <textarea id="next_step" name="next_step" rows="2" class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Next plan of action..."></textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="media" class="block text-[10px] font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Media Upload</label>
                    <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none">
                    <span class="text-[9px] text-slate-400 dark:text-slate-550 block mt-1">Upload site photos or documents. Max 5MB per file.</span>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md mt-2">
                    Submit Project Update
                </button>
            </form>
        @elseif($isLocked)
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 border border-slate-150 dark:border-slate-850 text-slate-500 dark:text-slate-400 text-xs rounded-xl text-center">
                This project is currently locked for updates by {{ $project->activeEditor?->name ?? 'another member' }}.
            </div>
        @else
            @if($lockExpired)
                <div class="p-3 bg-amber-50 dark:bg-amber-955/20 border border-amber-100 dark:border-amber-900/50 text-amber-800 dark:text-amber-400 text-xs rounded-xl">
                    Previous edit lock expired. You can claim the session.
                </div>
            @endif
            <form method="POST" action="{{ route('field.projects.start-update', $project) }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md">
                    Claim Lock &amp; Continue Project
                </button>
            </form>
        @endif
    </div>

    <!-- Update Timeline -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-50 dark:border-slate-850 pb-2.5">Update Timeline</h2>
        
        @if($project->updates->count() === 0)
            <div class="text-xs text-slate-400 dark:text-slate-500 text-center py-4 font-semibold">
                No project updates submitted yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($project->updates as $update)
                    @php($updateReviewStatus = $update->review_status ?? 'pending_review')
                    <div class="bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-850 rounded-2xl p-4 space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold text-slate-400 dark:text-slate-500 block">{{ $update->work_date?->format('d M Y') ?? $update->created_at?->format('d M Y') }}</span>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-white mt-0.5">{{ $update->summary ?: 'Project update' }}</h3>
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5 block">By {{ $update->user?->name ?? 'Staff' }} on {{ $update->created_at?->format('d M Y H:i') }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase whitespace-nowrap
                                {{ $updateReviewStatus === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : ($updateReviewStatus === 'needs_correction' ? 'bg-rose-50 dark:bg-rose-955/20 text-rose-700 dark:text-rose-455 border border-rose-100 dark:border-rose-900/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700') }}">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($updateReviewStatus)) }}
                            </span>
                        </div>

                        @if($updateReviewStatus === 'needs_correction')
                            <div class="p-2.5 bg-rose-50 dark:bg-rose-955/20 border border-rose-100 dark:border-rose-900/50 text-rose-800 dark:text-rose-400 text-[10px] rounded-lg">
                                <strong>Correction Notes:</strong> {{ $update->review_notes ?: 'Notes unavailable.' }}
                            </div>
                        @endif

                        @if($update->progress_percentage !== null)
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Progress: <strong class="text-slate-800 dark:text-slate-200">{{ $update->progress_percentage }}%</strong></div>
                        @endif

                        <div class="space-y-2 border-t border-slate-100 dark:border-slate-850 pt-2.5 text-xs text-slate-600 dark:text-slate-400">
                            @if($update->work_done)
                                <div><strong>Work Done:</strong> <p class="mt-0.5 leading-relaxed text-slate-700 dark:text-slate-300">{{ $update->work_done }}</p></div>
                            @endif
                            @if($update->materials_used)
                                <div><strong>Materials Used:</strong> <p class="mt-0.5 leading-relaxed text-slate-700 dark:text-slate-300">{{ $update->materials_used }}</p></div>
                            @endif
                            @if($update->issues_encountered)
                                <div><strong>Issues Encountered:</strong> <p class="mt-0.5 leading-relaxed text-rose-700 dark:text-rose-400">{{ $update->issues_encountered }}</p></div>
                            @endif
                            @if($update->next_step)
                                <div><strong>Next Step:</strong> <p class="mt-0.5 leading-relaxed text-slate-700 dark:text-slate-300">{{ $update->next_step }}</p></div>
                            @endif
                        </div>

                        @if($update->media->count())
                            <div class="grid grid-cols-2 gap-2 border-t border-slate-100 dark:border-slate-850 pt-2.5">
                                @foreach($update->media as $media)
                                    @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2 min-w-0 flex items-center gap-2">
                                        <span class="text-base">📄</span>
                                        <div class="min-w-0 flex-1 text-[9px]">
                                            @if($mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="text-indigo-650 dark:text-indigo-400 font-bold truncate block hover:underline">{{ $media->file_name ?? basename($media->file_path) }}</a>
                                            @else
                                                <span class="font-bold text-slate-700 dark:text-slate-300 truncate block">{{ $media->file_name ?? basename($media->file_path) }}</span>
                                            @endif
                                            <span class="text-slate-400 dark:text-slate-500 block mt-0.5">{{ number_format($media->file_size / 1024, 1) }} KB</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
