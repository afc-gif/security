<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $inspection->inspection_code }}</title>
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
        textarea, input[type="file"] { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 10px; font: inherit; background: #fff; }
        .form-row { margin-bottom: 14px; }
        .files { display: grid; gap: 8px; }
        .file { padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: #f9fafb; }
        .file a { color: #0f766e; font-weight: 700; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #e0f2fe; color: #075985; font-size: 13px; font-weight: 700; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.pending { background: #fef3c7; color: #92400e; }

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
                <h1>{{ $inspection->inspection_code }}</h1>
                <div class="muted">{{ $inspection->title }}</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link active" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link" href="{{ route('field.projects.index') }}">My Projects</a>
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
            <h2>Inspection Details</h2>
            <div class="grid">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $inspection->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $inspection->status }}">{{ ucfirst($inspection->status) }}</span>
                    </div>
                </div>
                <div>
                    <div class="label">Location</div>
                    <div class="value">{{ $inspection->location }}</div>
                </div>
                <div>
                    <div class="label">Scheduled Date</div>
                    <div class="value">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Inspection Type</div>
                    <div class="value">{{ $inspection->inspection_type ?: '—' }}</div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $inspection->priority ? ucfirst($inspection->priority) : '—' }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Inspection Report</h2>
            @if($inspection->status === 'completed')
                <div class="muted">This inspection has been submitted. Contact an administrator if changes are needed.</div>
                <div class="form-row" style="margin-top:14px;">
                    <label>Findings</label>
                    <div>{{ $inspection->findings ?: '—' }}</div>
                </div>
                <div class="form-row">
                    <label>Risks Identified</label>
                    <div>{{ $inspection->risks_identified ?: '—' }}</div>
                </div>
                <div class="form-row">
                    <label>Recommendations</label>
                    <div>{{ $inspection->recommendations ?: '—' }}</div>
                </div>
            @else
                <form method="POST" action="{{ route('field.inspections.submit', $inspection) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <label for="findings">Findings</label>
                        <textarea id="findings" name="findings" rows="5">{{ old('findings', $inspection->findings) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="risks_identified">Risks Identified</label>
                        <textarea id="risks_identified" name="risks_identified" rows="4">{{ old('risks_identified', $inspection->risks_identified) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="4">{{ old('recommendations', $inspection->recommendations) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="media">Evidence Files</label>
                        <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                        <div class="muted">JPG, PNG, or PDF. Maximum 5 MB per file.</div>
                    </div>
                    <button class="button" type="submit">Submit Report</button>
                </form>
            @endif
        </section>

        <section class="panel">
            <h2>Uploaded Files</h2>
            @if($inspection->media->count() === 0)
                <div class="muted">No files uploaded yet.</div>
            @else
                <div class="files">
                    @foreach($inspection->media as $media)
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
        </section>
    </main>
</body>
</html>
