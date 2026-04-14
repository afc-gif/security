<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects</title>
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
        .link, .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-weight: 700; text-decoration: none; }
        .link.active, .button { background: var(--action); border-color: var(--action); color: #fff; }
        .list { display: grid; gap: 12px; }
        .item { padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); }
        .item-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 10px; }
        .code { font-weight: 700; }
        .muted { color: var(--muted); font-size: 14px; }
        .meta { display: grid; gap: 6px; margin: 12px 0; color: var(--muted); font-size: 14px; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #e0f2fe; color: #075985; font-size: 13px; font-weight: 700; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.not_started { background: #e5e7eb; color: #374151; }
        .status.on_hold { background: #fef3c7; color: #92400e; }
        .progress { display: flex; align-items: center; gap: 10px; }
        .bar { height: 8px; flex: 1; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
        .bar span { display: block; height: 100%; background: var(--action); }
        .empty { padding: 24px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); color: var(--muted); text-align: center; }
        .pagination { margin-top: 16px; }

        @media (max-width: 640px) {
            .topbar, .item-head { flex-direction: column; align-items: stretch; }
            .link, .button { width: 100%; }
            .nav { display: grid; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <h1>My Projects</h1>
                <div class="muted">Assigned project work</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link active" href="{{ route('field.projects.index') }}">My Projects</a>
        </nav>

        @if (session('success'))
            <div class="item" style="border-color:#bbf7d0; background:#f0fdf4; color:#166534; margin-bottom:12px;">
                {{ session('success') }}
            </div>
        @endif

        @if($projects->count() === 0)
            <div class="empty">No projects assigned yet.</div>
        @else
            <div class="list">
                @foreach($projects as $project)
                    <article class="item">
                        <div class="item-head">
                            <div>
                                <div class="code">{{ $project->project_code }}</div>
                                <div class="muted">{{ $project->title }}</div>
                            </div>
                            <span class="status {{ $project->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}</span>
                        </div>
                        <div class="meta">
                            <div>Client: {{ $project->client?->client_name ?? '—' }}</div>
                            <div>Deadline: {{ $project->deadline?->format('d M Y') ?? '—' }}</div>
                            <div class="progress">
                                <div class="bar"><span style="width: {{ min(100, max(0, (int) ($project->progress_percentage ?? 0))) }}%;"></span></div>
                                <strong>{{ $project->progress_percentage ?? 0 }}%</strong>
                            </div>
                        </div>
                        <a class="button" href="{{ route('field.projects.show', $project) }}">Open Project</a>
                    </article>
                @endforeach
            </div>

            <div class="pagination">
                {{ $projects->links() }}
            </div>
        @endif
    </main>
</body>
</html>
