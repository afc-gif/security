@extends('layouts.field')

@section('title', $project->project_code . ' | ARTSCI')

@section('content')
<div class="space-y-6">
    @php
        $isLocked = $project->isBeingEdited();
        $lockExpired = $project->editingLockExpired();
        $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
        $isCompleted = $project->status === 'completed';
        $isReadyForReview = $project->status === 'ready_for_review';
        $latestProjectUpdate = $project->updates->first();
        $progress = min(100, max(0, (int) ($project->progress_percentage ?? 0)));
    @endphp

    <!-- Header banner -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">{{ $project->project_code }}</span>
            <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase
                {{ $isCompleted ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-sky-50 text-sky-700 border border-sky-100' }}">
                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
            </span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 mt-2">{{ $project->title }}</h1>
        <p class="text-xs text-slate-500 mt-1">Managed field requirements, progress milestones, and daily reports.</p>
    </div>

    <!-- Project details grid -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client</span>
            <strong class="block text-slate-800 mt-0.5">{{ $project->client?->client_name ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Deadline</span>
            <strong class="block text-slate-800 mt-0.5">{{ $project->deadline?->format('d M Y') ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Priority</span>
            <strong class="block text-slate-800 mt-0.5">{{ $project->priority ? ucfirst($project->priority) : '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Location</span>
            <strong class="block text-slate-800 mt-0.5 truncate" title="{{ $project->location }}">{{ $project->location ?: '-' }}</strong>
        </div>
        <div class="col-span-2 space-y-1.5 border-t border-slate-50 pt-2.5">
            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500">
                <span>Progress Percentage</span>
                <span class="text-slate-800">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-indigo-600 h-full rounded-full" @style(['width: ' . $progress . '%'])></div>
            </div>
        </div>
    </div>

    <!-- Requirements list -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Project Checklist</h2>
        
        @if($project->requirements->count() === 0)
            <div class="text-xs text-slate-400 text-center py-4 font-semibold">
                No approved requirements are attached to this project.
            </div>
        @else
            <div class="space-y-3">
                @foreach($project->requirements as $requirement)
                    <div class="flex items-start gap-3 p-3 bg-slate-50/50 border border-slate-100 rounded-xl">
                        <form method="POST" action="{{ route('field.projects.requirements.update', [$project, $requirement]) }}" class="mt-0.5">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_done" value="0">
                            <input type="checkbox" name="is_done" value="1" @checked($requirement->is_done) onchange="this.form.submit()" @disabled($isCompleted || $isReadyForReview) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        </form>
                        <div class="min-w-0 flex-1 text-xs">
                            <span class="text-[9px] font-extrabold uppercase text-slate-400">{{ $requirement->type }}</span>
                            <span class="block font-bold text-slate-800 mt-0.5">{{ $requirement->name }}</span>
                            @if($requirement->quantity)
                                <span class="text-[10px] text-slate-500 mt-0.5 block">Quantity: {{ $requirement->quantity }}</span>
                            @endif
                            @if($requirement->notes)
                                <span class="text-[10px] text-slate-500 block italic">"{{ $requirement->notes }}"</span>
                            @endif
                            @if($requirement->is_done)
                                <span class="text-[9px] text-emerald-600 font-semibold block mt-1">✓ Done by {{ $requirement->completedBy?->name ?? 'Staff' }} on {{ $requirement->completed_at?->format('d M Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Project Update Section -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Project Update</h2>

        @if($isCompleted)
            <div class="p-3.5 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-semibold rounded-xl text-center">
                Project completed.
            </div>
        @elseif($isReadyForReview)
            <div class="p-3.5 bg-sky-50 border border-sky-100 text-sky-800 text-xs font-semibold rounded-xl text-center">
                Project is waiting for coordinator or admin review.
            </div>
        @elseif($lockedByMe)
            <div class="p-3 bg-indigo-50 border border-indigo-100 text-indigo-800 text-xs rounded-xl">
                You have the update lock. Submit your report below or release the lock.
            </div>

            <form method="POST" action="{{ route('field.projects.release-update', $project) }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs py-2 px-4 rounded-xl transition-all">
                    Release Lock
                </button>
            </form>

            <form method="POST" action="{{ route('field.projects.submit-update', $project) }}" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                
                <div class="space-y-1">
                    <label for="summary" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Summary / Title *</label>
                    <input id="summary" type="text" name="summary" value="{{ old('summary') }}" required maxlength="255" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="e.g. Completed cabling setup">
                </div>

                <div class="space-y-1">
                    <label for="work_done" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Work Done *</label>
                    <textarea id="work_done" name="work_done" rows="4" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Details of progress made..."></textarea>
                </div>

                <div class="space-y-1">
                    <label for="materials_used" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Materials Used</label>
                    <textarea id="materials_used" name="materials_used" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="List items consumed on-site..."></textarea>
                </div>

                <div class="space-y-1">
                    <label for="issues_encountered" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Issues Encountered</label>
                    <textarea id="issues_encountered" name="issues_encountered" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Note any delays, faults, or errors..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="progress_percentage" class="block text-[9px] font-bold text-slate-700 uppercase tracking-wider">New Progress % *</label>
                        <input id="progress_percentage" type="number" name="progress_percentage" value="{{ old('progress_percentage', $project->progress_percentage ?? 0) }}" min="{{ (int) ($project->progress_percentage ?? 0) }}" max="100" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" required>
                    </div>
                    <div class="space-y-1">
                        <label for="work_date" class="block text-[9px] font-bold text-slate-700 uppercase tracking-wider">Work Date *</label>
                        <input id="work_date" type="date" name="work_date" value="{{ old('work_date', now()->format('Y-m-d')) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="next_step" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Next Step / Tasks</label>
                    <textarea id="next_step" name="next_step" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-indigo-500" placeholder="Next plan of action..."></textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="media" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Media Upload</label>
                    <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none">
                    <span class="text-[9px] text-slate-400 block mt-1">Upload site photos or documents. Max 5MB per file.</span>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md mt-2">
                    Submit Project Update
                </button>
            </form>
        @elseif($isLocked)
            <div class="p-3.5 bg-slate-50 border border-slate-150 text-slate-500 text-xs rounded-xl text-center">
                This project is currently locked for updates by {{ $project->activeEditor?->name ?? 'another member' }}.
            </div>
        @else
            @if($lockExpired)
                <div class="p-3 bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-xl">
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
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-2.5">Update Timeline</h2>
        
        @if($project->updates->count() === 0)
            <div class="text-xs text-slate-400 text-center py-4 font-semibold">
                No project updates submitted yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($project->updates as $update)
                    @php($updateReviewStatus = $update->review_status ?? 'pending_review')
                    <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold text-slate-400 block">{{ $update->work_date?->format('d M Y') ?? $update->created_at?->format('d M Y') }}</span>
                                <h3 class="text-xs font-bold text-slate-800 mt-0.5">{{ $update->summary ?: 'Project update' }}</h3>
                                <span class="text-[9px] text-slate-400 mt-0.5 block">By {{ $update->user?->name ?? 'Staff' }} on {{ $update->created_at?->format('d M Y H:i') }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase whitespace-nowrap
                                {{ $updateReviewStatus === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($updateReviewStatus === 'needs_correction' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($updateReviewStatus)) }}
                            </span>
                        </div>

                        @if($updateReviewStatus === 'needs_correction')
                            <div class="p-2.5 bg-rose-50 border border-rose-100 text-rose-800 text-[10px] rounded-lg">
                                <strong>Correction Notes:</strong> {{ $update->review_notes ?: 'Notes unavailable.' }}
                            </div>
                        @endif

                        @if($update->progress_percentage !== null)
                            <div class="text-[10px] text-slate-500">Progress: <strong class="text-slate-800">{{ $update->progress_percentage }}%</strong></div>
                        @endif

                        <div class="space-y-2 border-t border-slate-100 pt-2.5 text-xs text-slate-600">
                            @if($update->work_done)
                                <div><strong>Work Done:</strong> <p class="mt-0.5 leading-relaxed">{{ $update->work_done }}</p></div>
                            @endif
                            @if($update->materials_used)
                                <div><strong>Materials Used:</strong> <p class="mt-0.5 leading-relaxed">{{ $update->materials_used }}</p></div>
                            @endif
                            @if($update->issues_encountered)
                                <div><strong>Issues Encountered:</strong> <p class="mt-0.5 leading-relaxed text-rose-700">{{ $update->issues_encountered }}</p></div>
                            @endif
                            @if($update->next_step)
                                <div><strong>Next Step:</strong> <p class="mt-0.5 leading-relaxed">{{ $update->next_step }}</p></div>
                            @endif
                        </div>

                        @if($update->media->count())
                            <div class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-2.5">
                                @foreach($update->media as $media)
                                    @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                    <div class="bg-white border border-slate-200 rounded-xl p-2 min-w-0 flex items-center gap-2">
                                        <span class="text-base">📄</span>
                                        <div class="min-w-0 flex-1 text-[9px]">
                                            @if($mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="text-indigo-600 font-bold truncate block hover:underline">{{ $media->file_name ?? basename($media->file_path) }}</a>
                                            @else
                                                <span class="font-bold text-slate-700 truncate block">{{ $media->file_name ?? basename($media->file_path) }}</span>
                                            @endif
                                            <span class="text-slate-400 block mt-0.5">{{ number_format($media->file_size / 1024, 1) }} KB</span>
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
