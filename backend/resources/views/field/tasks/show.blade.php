<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $task->title }}</title>
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
        select { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 10px; font: inherit; background: #fff; }
        .form-row { margin-bottom: 14px; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #fef3c7; color: #92400e; font-size: 13px; font-weight: 700; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.in_progress { background: #e0f2fe; color: #075985; }

        @media (max-width: 640px) {
            .topbar { flex-direction: column; align-items: stretch; }
            .grid { grid-template-columns: 1fr; }
            .link { width: 100%; }
            .nav { display: grid; }
        }
    </style>
</head>
@php
    $linked = $task->assignable;
    $isInspection = $task->assignable_type === \App\Models\Inspection::class;
    $isProject = $task->assignable_type === \App\Models\Project::class;
    $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
    $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
    $linkedTitle = $linked?->title;
@endphp
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <h1>{{ $task->title }}</h1>
                <div class="muted">{{ $linkedType }}: {{ $linkedCode ?? 'Linked record unavailable' }}</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link" href="{{ route('field.projects.index') }}">My Projects</a>
            <a class="link active" href="{{ route('field.tasks.index') }}">My Tasks</a>
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
            <h2>Task Details</h2>
            <div class="grid">
                <div>
                    <div class="label">Status</div>
                    <div class="value"><span class="status {{ $task->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}</span></div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $task->priority ? ucfirst($task->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value">{{ $task->due_date?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Completed At</div>
                    <div class="value">{{ $task->completed_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Linked {{ $linkedType }}</h2>
            <div class="value">{{ $linkedCode ?? 'Linked record unavailable' }}</div>
            <div class="muted">{{ $linkedTitle ?? 'The linked inspection or project could not be found.' }}</div>
        </section>

        <section class="panel">
            <h2>Description</h2>
            <div>{{ $task->description ?: '—' }}</div>
        </section>

        <section class="panel">
            <h2>Update Status</h2>
            <form method="POST" action="{{ route('field.tasks.update-status', $task) }}">
                @csrf
                @method('PATCH')
                <div class="form-row">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="pending" @selected(old('status', $task->status) === 'pending')>Pending</option>
                        <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(old('status', $task->status) === 'completed')>Completed</option>
                    </select>
                </div>
                <button class="button" type="submit">Update Status</button>
            </form>
        </section>
    </main>
</body>
</html>
