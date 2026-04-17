@php
    $availableJobsCount = $availableJobsCount ?? 0;
    $myClaimedJobs = $myClaimedJobs ?? 0;
    $returnedJobs = $returnedJobs ?? 0;
    $overdueJobs = $overdueJobs ?? 0;
    $urgentJobs = $urgentJobs ?? collect();
    $recentJobs = $recentJobs ?? collect();
    $currentProjects = $currentProjects ?? collect();
    $currentProjectsCount = $currentProjectsCount ?? 0;

    $statusClass = function ($status, $isOverdue = false) {
        if ($isOverdue) {
            return 'overdue';
        }

        return match ($status) {
            \App\Models\JobRequestItem::STATUS_OPEN,
            \App\Models\JobRequestItem::STATUS_REOPENED => 'open',
            \App\Models\JobRequestItem::STATUS_CLAIMED => 'claimed',
            \App\Models\JobRequestItem::STATUS_SUBMITTED => 'submitted',
            \App\Models\JobRequestItem::STATUS_APPROVED => 'approved',
            \App\Models\JobRequestItem::STATUS_RETURNED => 'returned',
            \App\Models\JobRequestItem::STATUS_REJECTED => 'rejected',
            \App\Models\JobRequestItem::STATUS_OVERDUE => 'overdue',
            \App\Models\JobRequestItem::STATUS_CLOSED => 'closed',
            default => 'closed',
        };
    };

    $statusLabel = fn ($status) => str_replace('_', ' ', \Illuminate\Support\Str::title($status ?? 'unknown'));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTSCI Field</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header', [
            'availableJobsCount' => $availableJobsCount,
            'myClaimedJobs' => $myClaimedJobs ?? 0,
            'overdueJobs' => $overdueJobs ?? 0,
        ])

        <section class="section" aria-labelledby="dashboard-title">
            <p class="eyebrow">Field Dashboard</p>
            <h1 id="dashboard-title">Today's work queue</h1>
            <p class="subtext">Track claimable jobs, active submissions, projects, and tasks from one mobile-ready workspace.</p>
        </section>

        <section class="section" aria-labelledby="summary-title">
            <div class="section-heading">
                <h2 id="summary-title">Summary</h2>
            </div>

            <div class="summary-grid">
                <article class="summary-card blue">
                    <strong>{{ $availableJobsCount }}</strong>
                    <span>Available Jobs</span>
                    <small>Ready to claim</small>
                </article>
                <article class="summary-card blue">
                    <strong>{{ $myClaimedJobs ?? 0 }}</strong>
                    <span>My Claimed Jobs</span>
                    <small>In progress</small>
                </article>
                <article class="summary-card orange">
                    <strong>{{ $returnedJobs ?? 0 }}</strong>
                    <span>Returned Jobs</span>
                    <small>Needs fixing</small>
                </article>
                <article class="summary-card red">
                    <strong>{{ $overdueJobs ?? 0 }}</strong>
                    <span>Overdue Jobs</span>
                    <small>Requires attention</small>
                </article>
                <article class="summary-card green">
                    <strong>{{ $currentProjectsCount }}</strong>
                    <span>Current Projects</span>
                    <small>Visible to field staff</small>
                </article>
            </div>
        </section>

        <section class="section" aria-labelledby="projects-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Projects</p>
                    <h2 id="projects-title">Current Projects Available</h2>
                    <p class="subtext">Open project work all field staff can view and continue when available.</p>
                </div>
                <a class="button secondary mobile-full" href="{{ route('field.projects.index') }}">View All Projects</a>
            </div>

            @if($currentProjects->count() === 0)
                <div class="empty-state">
                    <div>No current projects are available right now.</div>
                    <a class="button secondary mobile-full" href="{{ route('field.projects.index') }}" style="margin-top:12px;">Open Projects Page</a>
                </div>
            @else
                <div class="card-grid">
                    @foreach($currentProjects as $project)
                        @php
                            $isLocked = $project->isBeingEdited();
                            $lockExpired = $project->editingLockExpired();
                            $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
                        @endphp
                        <article class="job-card">
                            <div class="card-head">
                                <div>
                                    <h3 class="card-title">{{ $project->project_code }}</h3>
                                    <p class="card-subtitle">{{ $project->title }}</p>
                                </div>
                                <span class="status {{ $project->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status ?? 'active')) }}</span>
                            </div>

                            <div class="meta">
                                <div>Client: {{ $project->client?->client_name ?? '-' }}</div>
                                <div>
                                    @if($lockExpired)
                                        Previous update session expired. You can continue this project.
                                    @elseif($lockedByMe)
                                        Update lock: You are updating this project
                                    @elseif($isLocked)
                                        Currently being updated by {{ $project->activeEditor?->name ?? 'another field staff member' }}
                                    @else
                                        Available to continue
                                    @endif
                                </div>
                                @php($progressPercentage = min(100, max(0, (int) ($project->progress_percentage ?? 0))))
                                <div class="progress">
                                    <div class="bar"><span @style(['width: ' . $progressPercentage . '%;'])></span></div>
                                    <strong>{{ $progressPercentage }}%</strong>
                                </div>
                            </div>

                            <a class="card-button" href="{{ route('field.projects.show', $project) }}">Open Project</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section" aria-labelledby="actions-title">
            <div class="section-heading">
                <h2 id="actions-title">Quick Actions</h2>
            </div>

            <div class="action-grid">
                <a class="action-card" href="{{ route('field.jobs.index') }}">
                    <strong>View Available Jobs</strong>
                    <span>&rarr;</span>
                </a>
                <a class="action-card" href="{{ route('field.jobs.index') }}">
                    <strong>Continue My Jobs</strong>
                    <span>&rarr;</span>
                </a>
                <a class="action-card" href="{{ route('field.projects.index') }}">
                    <strong>View Projects</strong>
                    <span>&rarr;</span>
                </a>
            </div>
        </section>

        <section class="section" aria-labelledby="urgent-title">
            <div class="section-heading">
                <div>
                    <h2 id="urgent-title">Urgent Items</h2>
                    <p class="subtext">Overdue and returned jobs appear here first.</p>
                </div>
            </div>

            @if($urgentJobs->count() === 0)
                <div class="empty-state">No urgent jobs right now.</div>
            @else
                <div class="job-grid">
                    @foreach($urgentJobs as $job)
                        @php
                            $isOverdue = $job->isOverdue();
                            $displayStatus = $isOverdue ? \App\Models\JobRequestItem::STATUS_OVERDUE : $job->status;
                            $buttonClass = $isOverdue ? 'danger' : ($job->status === \App\Models\JobRequestItem::STATUS_RETURNED ? 'warning' : '');
                            $buttonLabel = $job->status === \App\Models\JobRequestItem::STATUS_RETURNED ? 'Fix Submission' : 'Continue';
                        @endphp
                        <article class="job-card">
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? $job->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge {{ $statusClass($displayStatus, $isOverdue) }}">{{ $statusLabel($displayStatus) }}</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? 'Service category' }}</span>

                            <div class="job-meta">
                                <span class="{{ $isOverdue ? 'due-overdue' : '' }}">Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}</span>
                            </div>

                            <a class="card-button {{ $buttonClass }}" href="{{ route('field.jobs.show', $job) }}">{{ $buttonLabel }}</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section" aria-labelledby="activity-title">
            <div class="section-heading">
                <h2 id="activity-title">Recent Activity</h2>
            </div>

            @if($recentJobs->count() === 0)
                <div class="empty-state">No recent job activity yet.</div>
            @else
                <div class="activity-list">
                    @foreach($recentJobs as $job)
                        @php
                            $isOverdue = $job->isOverdue();
                            $displayStatus = $isOverdue ? \App\Models\JobRequestItem::STATUS_OVERDUE : $job->status;
                        @endphp
                        <a class="activity-item" href="{{ route('field.jobs.show', $job) }}">
                            <div>
                                <strong>{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</strong>
                                <span>{{ $job->serviceCategory?->name ?? 'Service category' }} &middot; {{ $job->updated_at?->format('d M Y H:i') ?? 'Recently updated' }}</span>
                            </div>
                            <span class="badge {{ $statusClass($displayStatus, $isOverdue) }}">{{ $statusLabel($displayStatus) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
