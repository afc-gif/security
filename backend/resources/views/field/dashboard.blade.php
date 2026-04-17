@php
    $fieldUser = auth()->user();
    $firstName = trim(explode(' ', $fieldUser?->name ?? 'Field Staff')[0]) ?: 'Field Staff';

    $availableJobsCount = $availableJobsCount ?? 0;
    $myJobsCount = $myJobsCount ?? 0;
    $overdueJobsCount = $overdueJobsCount ?? 0;
    $recentJobs = collect($recentJobs ?? []);
    $recentProjects = collect($recentProjects ?? []);

    $formatStatus = fn ($status) => str_replace('_', ' ', \Illuminate\Support\Str::title($status ?? 'Unknown'));
    $statusClass = fn ($status) => str_replace('_', '-', strtolower((string) ($status ?? 'unknown')));
    $isUrgentJob = function ($job): bool {
        $status = strtolower((string) ($job->status ?? ''));

        return in_array($status, ['returned', 'overdue'], true)
            || ($job->due_date && now()->greaterThan($job->due_date) && in_array($status, ['claimed', 'submitted'], true));
    };
    $priorityJobs = $recentJobs->filter($isUrgentJob)->take(3);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTSCI Field Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            --brand: #0b4aa2;
            --brand-dark: #07316c;
            --brand-soft: #eaf2ff;
            --green: #15803d;
            --green-soft: #dcfce7;
            --orange: #c2410c;
            --orange-soft: #ffedd5;
            --red: #b42318;
            --red-soft: #fee4e2;
            --ink: #101828;
            --muted: #667085;
            --line: #d9e2ec;
            --page: #f5f7fb;
            --card: #ffffff;
            --shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        html { background: var(--page); }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--ink);
            background:
                linear-gradient(180deg, rgba(11, 74, 162, 0.08) 0, rgba(245, 247, 251, 0) 260px),
                var(--page);
        }

        a { color: inherit; }
        h1, h2, h3, p { margin: 0; }

        .shell {
            width: min(1080px, 100%);
            margin: 0 auto;
            padding: 14px 14px 104px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            margin: -14px -14px 18px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid rgba(217, 226, 236, 0.88);
            background: rgba(245, 247, 251, 0.94);
            backdrop-filter: blur(16px);
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .logo {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(217, 226, 236, 0.95);
            border-radius: 8px;
            background: var(--card);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .app-name {
            font-size: 17px;
            line-height: 1.1;
            font-weight: 900;
        }

        .hello {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.2;
            font-weight: 800;
        }

        .logout {
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--card);
            color: var(--brand-dark);
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .intro {
            padding: 4px 2px 2px;
        }

        .eyebrow {
            color: var(--brand);
            font-size: 12px;
            font-weight: 900;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        h1 {
            margin-top: 7px;
            font-size: 30px;
            line-height: 1.08;
            font-weight: 900;
        }

        .subtext {
            margin-top: 8px;
            max-width: 620px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
            font-weight: 650;
        }

        .section {
            margin-top: 26px;
        }

        .section-head {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
        }

        h2 {
            font-size: 19px;
            line-height: 1.2;
            font-weight: 900;
        }

        .section-note {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 700;
        }

        .summary-grid,
        .actions,
        .list-grid {
            display: grid;
            gap: 12px;
        }

        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .summary-card,
        .action-card,
        .item-card,
        .empty-state,
        .priority-panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--card);
            box-shadow: var(--shadow);
        }

        .summary-card {
            min-height: 104px;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
        }

        .summary-value {
            font-size: 31px;
            line-height: 1;
            font-weight: 950;
        }

        .summary-label {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.2;
            font-weight: 900;
        }

        .summary-card.available .summary-value { color: var(--brand); }
        .summary-card.mine .summary-value { color: var(--green); }
        .summary-card.overdue .summary-value { color: var(--red); }

        .priority-panel {
            padding: 14px;
            display: grid;
            gap: 10px;
            border-color: rgba(180, 35, 24, 0.22);
            background: linear-gradient(180deg, #fff 0%, #fff9f8 100%);
        }

        .priority-item {
            padding: 12px;
            display: grid;
            gap: 10px;
            border: 1px solid rgba(217, 226, 236, 0.95);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.88);
        }

        .priority-row,
        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-size: 16px;
            line-height: 1.25;
            font-weight: 900;
        }

        .card-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 700;
        }

        .badge {
            min-height: 28px;
            padding: 6px 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef2f6;
            color: #475467;
            font-size: 12px;
            line-height: 1;
            font-weight: 900;
            white-space: nowrap;
        }

        .badge.open,
        .badge.claimed,
        .badge.submitted,
        .badge.ongoing,
        .badge.in-progress,
        .badge.not-started {
            background: var(--brand-soft);
            color: var(--brand-dark);
        }

        .badge.returned,
        .badge.reopened {
            background: var(--orange-soft);
            color: var(--orange);
        }

        .badge.overdue {
            background: var(--red-soft);
            color: var(--red);
        }

        .badge.approved,
        .badge.completed {
            background: var(--green-soft);
            color: var(--green);
        }

        .actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .action-card {
            min-height: 82px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: var(--brand-dark);
            text-decoration: none;
        }

        .action-card span {
            font-size: 16px;
            line-height: 1.2;
            font-weight: 950;
        }

        .action-card b {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            font-size: 12px;
            font-weight: 950;
        }

        .item-card {
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .meta {
            display: grid;
            gap: 7px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 700;
        }

        .button {
            width: 100%;
            min-height: 46px;
            padding: 11px 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--brand);
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
        }

        .button.secondary {
            background: var(--card);
            color: var(--brand-dark);
        }

        .progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bar {
            height: 8px;
            flex: 1;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--brand);
        }

        .empty-state {
            padding: 20px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
            font-weight: 800;
            text-align: center;
        }

        .bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 12px;
            z-index: 30;
            width: min(430px, calc(100% - 24px));
            transform: translateX(-50%);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
            padding: 7px;
            border: 1px solid rgba(217, 226, 236, 0.95);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(16px);
        }

        .bottom-nav a {
            min-height: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1;
            font-weight: 950;
            text-decoration: none;
        }

        .bottom-nav b {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            border-radius: 8px;
            background: #eef2f6;
            color: inherit;
            font-size: 10px;
            font-weight: 950;
        }

        .bottom-nav a.active {
            background: var(--brand);
            color: #fff;
        }

        .bottom-nav a.active b {
            background: rgba(255, 255, 255, 0.18);
        }

        @media (max-width: 359px) {
            .summary-grid { grid-template-columns: 1fr; }
            .actions { grid-template-columns: 1fr; }
            h1 { font-size: 27px; }
        }

        @media (min-width: 700px) {
            .shell { padding: 24px 24px 112px; }
            .topbar { margin: -24px -24px 24px; padding: 16px 24px; }
            .list-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            h1 { font-size: 36px; }
        }

        @media (min-width: 980px) {
            .dashboard-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 22px;
                align-items: start;
            }

            .dashboard-grid .section { margin-top: 0; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <div class="brand">
                <div class="logo">
                    <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI logo">
                </div>
                <div>
                    <p class="app-name">ARTSCI Field</p>
                    <p class="hello">Hi, {{ $firstName }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Logout</button>
            </form>
        </header>

        <section class="intro" aria-labelledby="dashboard-title">
            <p class="eyebrow">Field Dashboard</p>
            <h1 id="dashboard-title">Your work at a glance</h1>
            <p class="subtext">Start with urgent jobs, then continue your assigned jobs or project updates.</p>
        </section>

        <section class="section" aria-labelledby="summary-title">
            <div class="section-head">
                <h2 id="summary-title">Today</h2>
            </div>

            <div class="summary-grid">
                <article class="summary-card available">
                    <div class="summary-value">{{ $availableJobsCount }}</div>
                    <div class="summary-label">Available Jobs</div>
                </article>
                <article class="summary-card mine">
                    <div class="summary-value">{{ $myJobsCount }}</div>
                    <div class="summary-label">My Jobs</div>
                </article>
                <article class="summary-card overdue">
                    <div class="summary-value">{{ $overdueJobsCount }}</div>
                    <div class="summary-label">Overdue</div>
                </article>
            </div>
        </section>

        <section class="section" aria-labelledby="priority-title">
            <div class="section-head">
                <div>
                    <h2 id="priority-title">Priority</h2>
                    <p class="section-note">Returned and overdue jobs appear first.</p>
                </div>
            </div>

            @if($priorityJobs->isEmpty())
                <div class="empty-state">No returned or overdue jobs right now.</div>
            @else
                <div class="priority-panel">
                    @foreach($priorityJobs as $job)
                        <article class="priority-item">
                            <div class="priority-row">
                                <div>
                                    <h3 class="card-title">{{ $job->jobRequest?->title ?? $job->title ?? 'Job request unavailable' }}</h3>
                                    <p class="card-subtitle">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</p>
                                </div>
                                <span class="badge {{ $statusClass($job->status) }}">{{ $formatStatus($job->status) }}</span>
                            </div>
                            <div class="meta">
                                <div>Category: {{ $job->serviceCategory?->name ?? 'Service category' }}</div>
                                <div>Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}</div>
                            </div>
                            <a class="button" href="{{ route('field.jobs.show', $job) }}">Open Job</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section" aria-labelledby="actions-title">
            <div class="section-head">
                <h2 id="actions-title">Quick Actions</h2>
            </div>

            <div class="actions">
                <a class="action-card" href="{{ route('field.jobs.index') }}">
                    <span>View Jobs</span>
                    <b>JB</b>
                </a>
                <a class="action-card" href="{{ route('field.projects.index') }}">
                    <span>View Projects</span>
                    <b>PR</b>
                </a>
            </div>
        </section>

        <div class="dashboard-grid section">
            <section aria-labelledby="jobs-title">
                <div class="section-head">
                    <div>
                        <h2 id="jobs-title">Recent Jobs</h2>
                        <p class="section-note">Latest jobs assigned to you.</p>
                    </div>
                </div>

                @if($recentJobs->isEmpty())
                    <div class="empty-state">No recent jobs yet.</div>
                @else
                    <div class="list-grid">
                        @foreach($recentJobs as $job)
                            <article class="item-card">
                                <div class="card-top">
                                    <div>
                                        <h3 class="card-title">{{ $job->jobRequest?->title ?? $job->title ?? 'Job request unavailable' }}</h3>
                                        <p class="card-subtitle">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</p>
                                    </div>
                                    <span class="badge {{ $statusClass($job->status) }}">{{ $formatStatus($job->status) }}</span>
                                </div>
                                <div class="meta">
                                    <div>Category: {{ $job->serviceCategory?->name ?? 'Service category' }}</div>
                                    <div>Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                                <a class="button secondary" href="{{ route('field.jobs.show', $job) }}">Open Job</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="projects-title">
                <div class="section-head">
                    <div>
                        <h2 id="projects-title">Recent Projects</h2>
                        <p class="section-note">Current project work visible to field staff.</p>
                    </div>
                </div>

                @if($recentProjects->isEmpty())
                    <div class="empty-state">No current projects are available right now.</div>
                @else
                    <div class="list-grid">
                        @foreach($recentProjects as $project)
                            @php
                                $progress = min(100, max(0, (int) ($project->progress_percentage ?? 0)));
                                $isLocked = $project->isBeingEdited();
                                $lockExpired = $project->editingLockExpired();
                                $lockedByMe = $isLocked && (int) $project->active_editor_id === (int) auth()->id();
                            @endphp
                            <article class="item-card">
                                <div class="card-top">
                                    <div>
                                        <h3 class="card-title">{{ $project->title ?? $project->project_code ?? 'Project' }}</h3>
                                        <p class="card-subtitle">{{ $project->client?->client_name ?? 'Client unavailable' }}</p>
                                    </div>
                                    <span class="badge {{ $statusClass($project->status) }}">{{ $formatStatus($project->status ?? 'active') }}</span>
                                </div>
                                <div class="meta">
                                    <div class="progress">
                                        <div class="bar"><span style="width: {{ $progress }}%;"></span></div>
                                        <strong>{{ $progress }}%</strong>
                                    </div>
                                    <div>
                                        @if($lockExpired)
                                            Previous update session expired.
                                        @elseif($lockedByMe)
                                            You are updating this project.
                                        @elseif($isLocked)
                                            Being updated by {{ $project->activeEditor?->name ?? 'another field staff member' }}.
                                        @else
                                            Available to continue.
                                        @endif
                                    </div>
                                </div>
                                <a class="button secondary" href="{{ route('field.projects.show', $project) }}">Open Project</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>

    <nav class="bottom-nav" aria-label="Field navigation">
        <a class="active" href="{{ route('field.dashboard') }}" aria-current="page">
            <b>DB</b>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('field.jobs.index') }}">
            <b>JB</b>
            <span>Jobs</span>
        </a>
        <a href="{{ route('field.projects.index') }}">
            <b>PR</b>
            <span>Projects</span>
        </a>
    </nav>
</body>
</html>
