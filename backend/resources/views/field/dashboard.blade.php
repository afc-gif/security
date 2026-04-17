<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Staff Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            --text: #111827;
            --muted: #4b5563;
            --border: #d1d5db;
            --surface: #ffffff;
            --page: #f3f4f6;
            --accent: #047857;
            --action: #0f766e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: var(--page);
        }

        .page {
            width: min(960px, 100%);
            margin: 0 auto;
            padding: 24px 16px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
        }

        .logout,
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .button.active {
            background: var(--action);
            border-color: var(--action);
            color: #fff;
        }

        .button.primary {
            background: var(--action);
            border-color: var(--action);
            color: #fff;
        }

        .panel {
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 32px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 20px 0;
        }

        .stat {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #f9fafb;
        }

        .stat strong {
            display: block;
            font-size: 28px;
            line-height: 1;
        }

        .stat span {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 640px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            h1 { font-size: 26px; }
            .stats { grid-template-columns: 1fr; }
            .button, .logout { width: 100%; }
            .nav { display: grid; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div class="brand">{{ config('app.name', 'Security') }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="button active" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="button" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="button" href="{{ route('field.jobs.index') }}">Field Jobs</a>
            <a class="button" href="{{ route('field.projects.index') }}">My Projects</a>
            <a class="button" href="{{ route('field.tasks.index') }}">My Tasks</a>
        </nav>

        <section class="panel" aria-labelledby="field-dashboard-title">
            <p class="eyebrow">Field Staff</p>
            <h1 id="field-dashboard-title">Field staff dashboard</h1>
            <p>Track assigned inspections, project work, and site reports.</p>

            <div class="stats">
                <div class="stat">
                    <strong>{{ $totalInspections ?? 0 }}</strong>
                    <span>Total inspections</span>
                </div>
                <div class="stat">
                    <strong>{{ $pendingInspections ?? 0 }}</strong>
                    <span>Pending inspections</span>
                </div>
                <div class="stat">
                    <strong>{{ $completedInspections ?? 0 }}</strong>
                    <span>Completed inspections</span>
                </div>
                <div class="stat">
                    <strong>{{ $assignedProjects ?? 0 }}</strong>
                    <span>Assigned projects</span>
                </div>
                <div class="stat">
                    <strong>{{ $ongoingProjects ?? 0 }}</strong>
                    <span>Ongoing projects</span>
                </div>
                <div class="stat">
                    <strong>{{ $completedProjects ?? 0 }}</strong>
                    <span>Completed projects</span>
                </div>
                <div class="stat">
                    <strong>{{ $assignedTasks ?? 0 }}</strong>
                    <span>Assigned tasks</span>
                </div>
                <div class="stat">
                    <strong>{{ $pendingTasks ?? 0 }}</strong>
                    <span>Pending tasks</span>
                </div>
                <div class="stat">
                    <strong>{{ $completedTasks ?? 0 }}</strong>
                    <span>Completed tasks</span>
                </div>
            </div>

            <div class="nav" style="margin:0;">
                <a class="button primary" href="{{ route('field.inspections.index') }}">My Inspections</a>
                <a class="button primary" href="{{ route('field.jobs.index') }}">Field Jobs</a>
                <a class="button primary" href="{{ route('field.projects.index') }}">My Projects</a>
                <a class="button primary" href="{{ route('field.tasks.index') }}">My Tasks</a>
            </div>
        </section>
    </main>
</body>
</html>
