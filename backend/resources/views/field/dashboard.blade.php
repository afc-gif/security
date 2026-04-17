@php
    $userId = auth()->id();

    $availableJobsCount = \App\Models\JobRequestItem::query()
        ->available()
        ->when($userId, function ($query) use ($userId) {
            $query->whereNotExists(function ($attemptQuery) use ($userId) {
                $attemptQuery->selectRaw('1')
                    ->from('job_item_attempts')
                    ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                    ->where('job_item_attempts.user_id', $userId)
                    ->where('job_item_attempts.status', \App\Models\JobItemAttempt::STATUS_REJECTED);
            });
        })
        ->count();

    $urgentJobs = \App\Models\JobRequestItem::query()
        ->with(['jobRequest.client', 'serviceCategory'])
        ->where('claimed_by', $userId)
        ->where(function ($query) {
            $query->where('status', \App\Models\JobRequestItem::STATUS_RETURNED)
                ->orWhere('status', \App\Models\JobRequestItem::STATUS_OVERDUE)
                ->orWhere(function ($overdueQuery) {
                    $overdueQuery->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->whereIn('status', [
                            \App\Models\JobRequestItem::STATUS_CLAIMED,
                            \App\Models\JobRequestItem::STATUS_RETURNED,
                        ]);
                });
        })
        ->orderByRaw("CASE WHEN status = ? THEN 0 ELSE 1 END", [\App\Models\JobRequestItem::STATUS_OVERDUE])
        ->orderBy('due_date')
        ->limit(4)
        ->get();

    $recentJobs = \App\Models\JobRequestItem::query()
        ->with(['jobRequest.client', 'serviceCategory'])
        ->where('claimed_by', $userId)
        ->whereIn('status', [
            \App\Models\JobRequestItem::STATUS_CLAIMED,
            \App\Models\JobRequestItem::STATUS_SUBMITTED,
            \App\Models\JobRequestItem::STATUS_APPROVED,
            \App\Models\JobRequestItem::STATUS_RETURNED,
            \App\Models\JobRequestItem::STATUS_OVERDUE,
        ])
        ->latest('updated_at')
        ->limit(4)
        ->get();

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
            </div>
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
                <a class="action-card" href="{{ route('field.tasks.index') }}">
                    <strong>View Tasks</strong>
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
