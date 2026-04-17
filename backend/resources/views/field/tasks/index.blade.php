<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="tasks-title">
            <p class="eyebrow">Tasks</p>
            <h1 id="tasks-title">My Tasks</h1>
            <p class="subtext">Review assigned work items and update progress from the field.</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        <section class="section">
            @if($tasks->count() === 0)
                <div class="empty-state">No tasks assigned yet.</div>
            @else
                <div class="card-grid">
                    @foreach($tasks as $task)
                        @php
                            $linked = $task->assignable;
                            $isInspection = $task->assignable_type === \App\Models\Inspection::class;
                            $isProject = $task->assignable_type === \App\Models\Project::class;
                            $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
                            $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
                        @endphp
                        <article class="job-card">
                            <div class="card-head">
                                <div>
                                    <h3 class="card-title">{{ $task->title }}</h3>
                                    <p class="card-subtitle">{{ $linkedType }}: {{ $linkedCode ?? 'Linked record unavailable' }}</p>
                                </div>
                                <span class="status {{ $task->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}</span>
                            </div>

                            <div class="meta">
                                <div>Due: {{ $task->due_date?->format('d M Y H:i') ?? '-' }}</div>
                                <div>Priority: {{ $task->priority ? ucfirst($task->priority) : '-' }}</div>
                            </div>

                            <a class="card-button" href="{{ route('field.tasks.show', $task) }}">Open Task</a>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $tasks->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
