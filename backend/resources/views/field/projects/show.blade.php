<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->project_code }}</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        @php
            $isLocked = $project->isBeingEdited();
            $lockExpired = $project->editingLockExpired();
            $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
            $isCompleted = $project->status === 'completed';
            $latestProjectUpdate = $project->updates->first();
        @endphp

        <section class="section" aria-labelledby="project-title">
            <p class="eyebrow">Project Details</p>
            <h1 id="project-title">{{ $project->project_code }}</h1>
            <p class="subtext">{{ $project->title }}</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="panel">
            <h2>Project Details</h2>
            <div class="grid">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $project->client?->client_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $project->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}</span>
                    </div>
                </div>
                <div>
                    <div class="label">Progress</div>
                    <div class="value progress">
                        <div class="bar"><span style="width: {{ min(100, max(0, (int) ($project->progress_percentage ?? 0))) }}%;"></span></div>
                        <span>{{ $project->progress_percentage ?? 0 }}%</span>
                    </div>
                </div>
                <div>
                    <div class="label">Last Updated By</div>
                    <div class="value">
                        {{ $latestProjectUpdate?->user?->name ?? '-' }}
                        @if($latestProjectUpdate?->created_at)
                            <span class="muted">on {{ $latestProjectUpdate->created_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="label">Update Lock</div>
                    <div class="value">
                        @if($isCompleted)
                            Project completed.
                        @elseif($lockExpired)
                            Previous update session expired. You can continue this project.
                        @elseif($lockedByMe)
                            You are currently updating this project.
                        @elseif($isLocked)
                            Currently being updated by {{ $project->activeEditor?->name ?? 'another field staff member' }}.
                        @else
                            Available to continue.
                        @endif
                    </div>
                </div>
                @if($project->editing_started_at && ($isLocked || $lockExpired))
                    <div>
                        <div class="label">{{ $lockExpired ? 'Expired Session Started' : 'Editing Started' }}</div>
                        <div class="value">{{ $project->editing_started_at->format('d M Y H:i') }}</div>
                    </div>
                @endif
                <div>
                    <div class="label">Deadline</div>
                    <div class="value">{{ $project->deadline?->format('d M Y') ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Location</div>
                    <div class="value">{{ $project->location ?: '-' }}</div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $project->priority ? ucfirst($project->priority) : '-' }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Project Update</h2>

            @if($isCompleted)
                <div class="notice success" style="margin-bottom:0;">
                    Project completed.
                </div>
            @elseif($lockedByMe)
                <div class="notice locked">
                    You have the update turn. Submit the next update or release the lock when you are done.
                </div>

                <form method="POST" action="{{ route('field.projects.release-update', $project) }}" style="margin-bottom:14px;">
                    @csrf
                    <button class="button secondary full" type="submit">Release Lock</button>
                </form>

                <form method="POST" action="{{ route('field.projects.submit-update', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <label for="summary">Summary</label>
                        <input id="summary" type="text" name="summary" value="{{ old('summary') }}" maxlength="255">
                    </div>
                    <div class="form-row">
                        <label for="work_done">Work Done</label>
                        <textarea id="work_done" name="work_done" rows="4">{{ old('work_done') }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="materials_used">Materials Used</label>
                        <textarea id="materials_used" name="materials_used" rows="3">{{ old('materials_used') }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="issues_encountered">Issues Encountered</label>
                        <textarea id="issues_encountered" name="issues_encountered" rows="3">{{ old('issues_encountered') }}</textarea>
                    </div>
                    <div class="grid">
                        <div class="form-row">
                            <label for="progress_percentage">Progress Percentage</label>
                            <input id="progress_percentage" type="number" name="progress_percentage" value="{{ old('progress_percentage', $project->progress_percentage ?? 0) }}" min="{{ (int) ($project->progress_percentage ?? 0) }}" max="100">
                            <div class="muted">Current progress is {{ $project->progress_percentage ?? 0 }}%. Progress cannot move backwards.</div>
                        </div>
                        <div class="form-row">
                            <label for="work_date">Work Date</label>
                            <input id="work_date" type="date" name="work_date" value="{{ old('work_date') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="next_step">Next Step</label>
                        <textarea id="next_step" name="next_step" rows="3">{{ old('next_step') }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="media">Photos or Documents</label>
                        <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                        <div class="muted">JPG, PNG, or PDF. Maximum 5 MB per file.</div>
                    </div>
                    <button class="button full" type="submit">Submit Update</button>
                </form>
            @elseif($isLocked)
                <div class="notice locked" style="margin-bottom:0;">
                    This project is currently being updated by {{ $project->activeEditor?->name ?? 'another field staff member' }}.
                </div>
            @else
                @if($lockExpired)
                    <div class="notice locked">
                        Previous update session expired. You can continue this project.
                    </div>
                @endif
                <form method="POST" action="{{ route('field.projects.start-update', $project) }}">
                    @csrf
                    <button class="button full" type="submit">Continue Project</button>
                </form>
            @endif
        </section>

        <section class="panel">
            <h2>Update Timeline</h2>
            @if($project->updates->count() === 0)
                <div class="muted">No project updates submitted yet.</div>
            @else
                <div class="timeline">
                    @foreach($project->updates as $update)
                        <article class="update">
                            <div class="label">{{ $update->work_date?->format('d M Y') ?? $update->created_at?->format('d M Y') }}</div>
                            <div class="value">{{ $update->summary ?: 'Project update' }}</div>
                            <div class="muted">Submitted by {{ $update->user?->name ?? '-' }} on {{ $update->created_at?->format('d M Y H:i') ?? '-' }}</div>
                            @php($updateReviewStatus = $update->review_status ?? 'pending_review')
                            <div class="muted" style="margin-top:8px;">
                                Review:
                                <span class="status {{ $updateReviewStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($updateReviewStatus)) }}</span>
                            </div>
                            @if($updateReviewStatus === 'needs_correction')
                                <div class="notice error" style="margin-top:10px; margin-bottom:0;">
                                    {{ $update->review_notes ?: 'This update needs correction. Contact an administrator for details.' }}
                                </div>
                            @endif
                            @if($update->progress_percentage !== null)
                                <div class="muted" style="margin-top:8px;">Progress: {{ $update->progress_percentage }}%</div>
                            @endif
                            @if($update->work_done)
                                <div class="form-row" style="margin-top:10px;"><strong>Work Done</strong><div>{{ $update->work_done }}</div></div>
                            @endif
                            @if($update->materials_used)
                                <div class="form-row"><strong>Materials Used</strong><div>{{ $update->materials_used }}</div></div>
                            @endif
                            @if($update->issues_encountered)
                                <div class="form-row"><strong>Issues Encountered</strong><div>{{ $update->issues_encountered }}</div></div>
                            @endif
                            @if($update->next_step)
                                <div class="form-row"><strong>Next Step</strong><div>{{ $update->next_step }}</div></div>
                            @endif
                            @if($update->media->count())
                                <div class="files">
                                    @foreach($update->media as $media)
                                        <div class="file">
                                            @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                            @if($mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">{{ $media->file_name ?? basename($media->file_path) }}</a>
                                            @else
                                                <strong>{{ $media->file_name ?? basename((string) $media->file_path) }}</strong>
                                                <div class="muted">File unavailable</div>
                                            @endif
                                            <div class="muted">{{ $media->file_type ?: 'File' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
