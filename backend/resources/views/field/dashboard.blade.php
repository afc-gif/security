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
            --blue: #0b4aa2;
            --blue-dark: #07316c;
            --green: #15803d;
            --red: #b42318;
            --yellow: #a16207;
            --orange: #c2410c;
            --ink: #101828;
            --muted: #667085;
            --line: #d9e2ec;
            --soft: #f4f7fb;
            --panel: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--ink);
            background: var(--soft);
        }

        a { color: inherit; }

        .shell {
            width: min(1040px, 100%);
            margin: 0 auto;
            padding: 14px 14px 96px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            margin: -14px -14px 18px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid rgba(217, 226, 236, 0.9);
            background: rgba(244, 247, 251, 0.96);
            backdrop-filter: blur(14px);
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .logo {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .app-name {
            margin: 0;
            font-size: 17px;
            line-height: 1.1;
            font-weight: 900;
        }

        .hello {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .logout {
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: var(--blue-dark);
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .hero,
        .section {
            margin-top: 22px;
        }

        .eyebrow {
            margin: 0 0 6px;
            color: var(--blue);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        h1 {
            font-size: 28px;
            line-height: 1.12;
            font-weight: 900;
        }

        h2 {
            font-size: 19px;
            line-height: 1.2;
            font-weight: 900;
        }

        .subtext {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .section-head {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .summary-grid,
        .actions,
        .card-grid {
            display: grid;
            gap: 12px;
        }

        .summary-card,
        .action-card,
        .item-card,
        .empty {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
        }

        .summary-card {
            min-height: 104px;
            padding: 15px;
        }

        .summary-card strong {
            display: block;
            font-size: 32px;
            line-height: 1;
            font-weight: 900;
        }

        .summary-card span {
            display: block;
            margin-top: 10px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 900;
        }

        .summary-card.available { color: var(--blue); }
        .summary-card.mine { color: var(--green); }
        .summary-card.overdue { color: var(--red); }

        .action-card {
            min-height: 68px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-decoration: none;
            font-weight: 900;
        }

        .action-card b {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: var(--blue);
            color: #fff;
        }

        .item-card {
            padding: 15px;
        }

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

        .card-subtitle,
        .meta {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 700;
        }

        .card-subtitle { margin-top: 4px; }
        .meta { margin-top: 12px; display: grid; gap: 7px; }

        .badge {
            min-height: 28px;
            padding: 5px 9px;
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

        .badge.claimed,
        .badge.submitted {
            background: #eaf2ff;
            color: var(--blue-dark);
        }

        .badge.returned {
            background: #ffedd5;
            color: var(--orange);
        }

        .badge.overdue {
            background: #fee4e2;
            color: var(--red);
        }

        .badge.ongoing,
        .badge.in-progress {
            background: #eaf2ff;
            color: var(--blue-dark);
        }

        .badge.completed {
            background: #dcfce7;
            color: var(--green);
        }

        .button {
            width: 100%;
            min-height: 44px;
            margin-top: 14px;
            padding: 10px 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--blue);
            border-radius: 8px;
            background: var(--blue);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
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
            background: var(--blue);
        }

        .empty {
            padding: 20px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 800;
            text-align: center;
        }

        .bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 12px;
            z-index: 30;
            width: min(460px, calc(100% - 24px));
            transform: translateX(-50%);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 5px;
            padding: 7px;
            border: 1px solid rgba(217, 226, 236, 0.95);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(16px);
        }

        .bottom-nav a {
            min-height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
            text-decoration: none;
        }

        .bottom-nav b {
            display: grid;
            place-items: center;
            width: 22px;
            height: 22px;
            border-radius: 8px;
            background: #eef2f6;
            color: inherit;
            font-size: 10px;
        }

        .bottom-nav a.active {
            background: var(--blue);
            color: #fff;
        }

        .bottom-nav a.active b {
            background: rgba(255, 255, 255, 0.18);
        }

        @media (min-width: 700px) {
            .shell { padding: 24px 24px 108px; }
            .topbar { margin: -24px -24px 22px; padding: 18px 24px; }
            .summary-grid,
            .actions,
            .card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
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

        <section class="hero" aria-labelledby="dashboard-title">
            <p class="eyebrow">Field Dashboard</p>
            <h1 id="dashboard-title">Today's work queue</h1>
            <p class="subtext">Claim jobs, continue active work, and check current project updates.</p>
        </section>

        <section class="section" aria-labelledby="summary-title">
            <div class="section-head">
                <h2 id="summary-title">Summary</h2>
            </div>

            <div class="summary-grid">
                <article class="summary-card available">
                    <strong>{{ $availableJobsCount }}</strong>
                    <span>Available Jobs</span>
                </article>
                <article class="summary-card mine">
                    <strong>{{ $myJobsCount }}</strong>
                    <span>My Jobs</span>
                </article>
                <article class="summary-card overdue">
                    <strong>{{ $overdueJobsCount }}</strong>
                    <span>Overdue</span>
                </article>
            </div>
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

        <section class="section" aria-labelledby="jobs-title">
            <div class="section-head">
                <div>
                    <h2 id="jobs-title">Recent Jobs</h2>
                    <p class="subtext">Your latest active job items.</p>
                </div>
            </div>

            @if($recentJobs->isEmpty())
                <div class="empty">No recent jobs yet.</div>
            @else
                <div class="card-grid">
                    @foreach($recentJobs as $job)
                        <article class="item-card">
                            <div class="card-top">
                                <div>
                                    <h3 class="card-title">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="card-subtitle">{{ $job->jobRequest?->title ?? $job->title ?? 'Job request unavailable' }}</p>
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

        <section class="section" aria-labelledby="projects-title">
            <div class="section-head">
                <div>
                    <h2 id="projects-title">Recent Projects</h2>
                    <p class="subtext">Current projects visible to field staff.</p>
                </div>
            </div>

            @if($recentProjects->isEmpty())
                <div class="empty">No current projects are available right now.</div>
            @else
                <div class="card-grid">
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
                                    <div class="bar"><span @style(['width: ' . $progress . '%;'])></span></div>
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
                            <a class="button" href="{{ route('field.projects.show', $project) }}">Open Project</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
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
