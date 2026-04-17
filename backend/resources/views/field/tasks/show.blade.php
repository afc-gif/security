@php
    $linked = $task->assignable;
    $isInspection = $task->assignable_type === \App\Models\Inspection::class;
    $isProject = $task->assignable_type === \App\Models\Project::class;
    $linkedType = $isInspection ? 'Inspection' : ($isProject ? 'Project' : 'Linked record');
    $linkedCode = $isInspection ? $linked?->inspection_code : ($isProject ? $linked?->project_code : null);
    $linkedTitle = $linked?->title;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $task->title }}</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="task-title">
            <p class="eyebrow">Task Details</p>
            <h1 id="task-title">{{ $task->title }}</h1>
            <p class="subtext">{{ $linkedType }}: {{ $linkedCode ?? 'Linked record unavailable' }}</p>
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
            <h2>Task Details</h2>
            <div class="grid">
                <div>
                    <div class="label">Status</div>
                    <div class="value"><span class="status {{ $task->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}</span></div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $task->priority ? ucfirst($task->priority) : '-' }}</div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value">{{ $task->due_date?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Completed At</div>
                    <div class="value">{{ $task->completed_at?->format('d M Y H:i') ?? '-' }}</div>
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
            <div>{{ $task->description ?: '-' }}</div>
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
                <button class="button full" type="submit">Update Status</button>
            </form>
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
