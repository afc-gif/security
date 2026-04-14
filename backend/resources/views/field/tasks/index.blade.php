<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
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
        .muted { color: var(--muted); font-size: 14px; }
        .meta { display: grid; gap: 6px; margin: 12px 0; color: var(--muted); font-size: 14px; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #fef3c7; color: #92400e; font-size: 13px; font-weight: 700; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.in_progress { background: #e0f2fe; color: #075985; }
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
                <h1>My Tasks</h1>
                <div class="muted">Assigned work items</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link" href="{{ route('field.projects.index') }}">My Projects</a>
            <a class="link active" href="{{ route('field.tasks.index') }}">My Tasks</a>
        </nav>

        @if (session('success'))
            <div class="item" style="border-color:#bbf7d0; background:#f0fdf4; color:#166534; margin-bottom:12px;">
                {{ session('success') }}
            </div>
        @endif

        @if($tasks->count() === 0)
            <div class="empty">No tasks assigned yet.</div>
        @else
            <div class="list">
                @foreach($tasks as $task)
                    @php
                        $linked = $task->assignable;
                        $isInspection = $task->assignable_type === \App\Models\Inspection::class;
                        $isProject = $task->assignable_type === \App\Models\Project::class;
                        $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
                        $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
                    @endphp
                    <article class="item">
                        <div class="item-head">
                            <div>
                                <strong>{{ $task->title }}</strong>
                                <div class="muted">{{ $linkedType }}: {{ $linkedCode ?? 'Linked record unavailable' }}</div>
                            </div>
                            <span class="status {{ $task->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}</span>
                        </div>
                        <div class="meta">
                            <div>Due: {{ $task->due_date?->format('d M Y H:i') ?? '—' }}</div>
                            <div>Priority: {{ $task->priority ? ucfirst($task->priority) : '—' }}</div>
                        </div>
                        <a class="button" href="{{ route('field.tasks.show', $task) }}">Open Task</a>
                    </article>
                @endforeach
            </div>

            <div class="pagination">
                {{ $tasks->links() }}
            </div>
        @endif
    </main>
</body>
</html>
