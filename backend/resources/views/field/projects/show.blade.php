<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->project_code }}</title>
    <style>
        :root {
            --text: #111827;
            --muted: #4b5563;
            --border: #d1d5db;
            --surface: #ffffff;
            --page: #f3f4f6;
            --action: #0f766e;
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Arial, Helvetica, sans-serif; color: var(--text); background: var(--page); }
        .page { width: min(960px, 100%); margin: 0 auto; padding: 20px 14px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        h1 { margin: 0; font-size: 28px; line-height: 1.2; }
        h2 { margin: 0 0 12px; font-size: 20px; }
        .link, .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-weight: 700; text-decoration: none; }
        .link.active, .button { background: var(--action); border-color: var(--action); color: #fff; }
        .button { width: 100%; }
        .panel { padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .label { color: var(--muted); font-size: 13px; font-weight: 700; text-transform: uppercase; }
        .value { margin-top: 4px; font-weight: 700; }
        .muted { color: var(--muted); font-size: 14px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 14px; }
        .alert.success { border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
        .alert.error { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
        label { display: block; margin-bottom: 6px; color: var(--muted); font-size: 14px; font-weight: 700; }
        textarea, input[type="text"], input[type="number"], input[type="date"], input[type="file"] { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 10px; font: inherit; background: #fff; }
        .form-row { margin-bottom: 14px; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #e0f2fe; color: #075985; font-size: 13px; font-weight: 700; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.not_started { background: #e5e7eb; color: #374151; }
        .status.on_hold { background: #fef3c7; color: #92400e; }
        .status.pending_review { background: #fef3c7; color: #92400e; }
        .status.reviewed { background: #dcfce7; color: #166534; }
        .status.needs_correction { background: #fee2e2; color: #991b1b; }
        .progress { display: flex; align-items: center; gap: 10px; }
        .bar { height: 8px; flex: 1; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
        .bar span { display: block; height: 100%; background: var(--action); }
        .timeline { display: grid; gap: 12px; }
        .update { padding: 14px; border: 1px solid var(--border); border-radius: 8px; background: #f9fafb; }
        .files { display: grid; gap: 8px; margin-top: 10px; }
        .file { padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #fff; }
        .file a { color: #0f766e; font-weight: 700; }

        @media (max-width: 640px) {
            .topbar { flex-direction: column; align-items: stretch; }
            .grid { grid-template-columns: 1fr; }
            .link { width: 100%; }
            .nav { display: grid; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <h1>{{ $project->project_code }}</h1>
                <div class="muted">{{ $project->title }}</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link active" href="{{ route('field.projects.index') }}">My Projects</a>
            <a class="link" href="{{ route('field.tasks.index') }}">My Tasks</a>
        </nav>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert error">
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
                    <div class="value">{{ $project->client?->client_name ?? '—' }}</div>
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
                    <div class="label">Deadline</div>
                    <div class="value">{{ $project->deadline?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Location</div>
                    <div class="value">{{ $project->location ?: '—' }}</div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $project->priority ? ucfirst($project->priority) : '—' }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Submit Progress Update</h2>
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
                        <input id="progress_percentage" type="number" name="progress_percentage" value="{{ old('progress_percentage') }}" min="0" max="100">
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
                <button class="button" type="submit">Submit Update</button>
            </form>
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
                            <div class="muted">Submitted by {{ $update->user?->name ?? '—' }} on {{ $update->created_at?->format('d M Y H:i') ?? '—' }}</div>
                            @php($updateReviewStatus = $update->review_status ?? 'pending_review')
                            <div class="muted" style="margin-top:8px;">
                                Review:
                                <span class="status {{ $updateReviewStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($updateReviewStatus)) }}</span>
                            </div>
                            @if($updateReviewStatus === 'needs_correction')
                                <div class="alert error" style="margin-top:10px; margin-bottom:0;">
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
                                            <div class="muted">{{ $media->file_type ?: 'File' }} @if($media->file_size) · {{ number_format($media->file_size / 1024, 1) }} KB @endif</div>
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
</body>
</html>
