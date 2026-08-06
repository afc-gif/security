<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="projects-title">
            <p class="eyebrow">Projects</p>
            <h1 id="projects-title">Projects</h1>
            <p class="subtext">All field staff can view projects. One person can continue an update at a time.</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        <section class="section">
            @if($projects->count() === 0)
                <div class="empty-state">No projects assigned yet.</div>
            @else
                <div class="card-grid">
                    @foreach($projects as $project)
                        @php
                            $isLocked = $project->isBeingEdited();
                            $lockExpired = $project->editingLockExpired();
                            $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
                            $isCompleted = $project->status === 'completed';
                            $isReadyForReview = $project->status === 'ready_for_review';
                        @endphp
                        <article class="job-card">
                            <div class="card-head">
                                <div>
                                    <h3 class="card-title">{{ $project->project_code }}</h3>
                                    <p class="card-subtitle">{{ $project->title }}</p>
                                </div>
                                <span class="status {{ $project->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}</span>
                            </div>

                            <div class="meta">
                                <div>Client: {{ $project->client?->client_name ?? '-' }}</div>
                                <div>Deadline: {{ $project->deadline?->format('d M Y') ?? '-' }}</div>
                                <div>
                                    @if($isCompleted)
                                        Project completed
                                    @elseif($isReadyForReview)
                                        Waiting for admin review
                                    @elseif($lockExpired)
                                        Previous update session expired. You can continue this project.
                                    @elseif($lockedByMe)
                                        Update lock: You are updating this project
                                    @elseif($isLocked)
                                        Currently being updated by {{ $project->activeEditor?->name ?? 'another field staff member' }}
                                    @else
                                        Available to continue
                                    @endif
                                </div>
                                <div class="progress">
                                    <div class="bar"><span style="width: {{ min(100, max(0, (int) ($project->progress_percentage ?? 0))) }}%;"></span></div>
                                    <strong>{{ $project->progress_percentage ?? 0 }}%</strong>
                                </div>
                            </div>

                            <a class="card-button" href="{{ route('field.projects.show', $project) }}">Open Project</a>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $projects->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
