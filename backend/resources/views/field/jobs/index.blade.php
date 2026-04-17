<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Jobs</title>
    <style>
        :root {
            --text: #111827;
            --muted: #4b5563;
            --border: #d1d5db;
            --surface: #ffffff;
            --page: #f3f4f6;
            --action: #0f766e;
            --action-dark: #115e59;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Arial, Helvetica, sans-serif; color: var(--text); background: var(--page); }
        .page { width: min(960px, 100%); margin: 0 auto; padding: 20px 14px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        h1 { margin: 0; font-size: 28px; line-height: 1.2; }
        h2 { margin: 0 0 12px; font-size: 22px; line-height: 1.2; }
        .link, .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-weight: 700; text-decoration: none; }
        .link.active, .button.primary { background: var(--action); border-color: var(--action); color: #fff; }
        .button.primary { width: 100%; min-height: 48px; cursor: pointer; }
        .button.primary:hover { background: var(--action-dark); border-color: var(--action-dark); }
        .button.secondary { width: 100%; margin-top: 8px; }
        .button[disabled] { background: #e5e7eb; border-color: #d1d5db; color: #6b7280; cursor: not-allowed; }
        .section { margin-top: 20px; }
        .list { display: grid; gap: 12px; }
        .item { padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); }
        .item-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
        .title { font-weight: 700; }
        .muted { color: var(--muted); font-size: 14px; }
        .meta { display: grid; gap: 6px; margin: 12px 0; color: var(--muted); font-size: 14px; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #e5e7eb; color: #374151; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .status.open { background: #e0f2fe; color: #075985; }
        .status.claimed { background: #dbeafe; color: #1d4ed8; }
        .status.submitted { background: #fef3c7; color: #92400e; }
        .status.returned { background: #ffedd5; color: #9a3412; }
        .status.approved { background: #dcfce7; color: #166534; }
        .status.reopened { background: #e0f2fe; color: #075985; }
        .status.overdue { background: #fee2e2; color: #991b1b; }
        .status.rejected { background: #fee2e2; color: #991b1b; }
        .deadline { font-weight: 700; }
        .deadline.today { color: #92400e; }
        .deadline.overdue { color: #991b1b; }
        .empty, .notice { padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); color: var(--muted); text-align: center; }
        .success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; margin-bottom: 12px; }
        .error { border-color: #fecaca; background: #fef2f2; color: var(--danger); margin-bottom: 12px; }
        .pagination { margin-top: 16px; }
        form { margin: 0; }

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
                <h1>Field Jobs</h1>
                <div class="muted">Claim available category items and track your active jobs.</div>
            </div>
        </div>

        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
            <a class="link" href="{{ route('field.inspections.index') }}">My Inspections</a>
            <a class="link active" href="{{ route('field.jobs.index') }}">Field Jobs</a>
            <a class="link" href="{{ route('field.projects.index') }}">My Projects</a>
            <a class="link" href="{{ route('field.tasks.index') }}">My Tasks</a>
        </nav>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="section" aria-labelledby="available-jobs-title">
            <h2 id="available-jobs-title">Available Jobs</h2>

            @if($availableJobs->count() === 0)
                <div class="empty">No available jobs right now.</div>
            @else
                <div class="list">
                    @foreach($availableJobs as $job)
                        <article class="item">
                            <div class="item-head">
                                <div>
                                    <div class="title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</div>
                                    <div class="muted">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</div>
                                </div>
                                <span class="status {{ $job->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($job->status)) }}</span>
                            </div>
                            <div class="meta">
                                <div>Client: {{ $job->jobRequest?->client?->client_name ?? '—' }}</div>
                                <div>Category: {{ $job->serviceCategory?->name ?? '—' }}</div>
                                <div>
                                    Due:
                                    <span class="deadline {{ $job->due_date?->isToday() ? 'today' : '' }}">
                                        {{ $job->due_date?->format('d M Y H:i') ?? '—' }}
                                        @if($job->due_date?->isToday())
                                            (due today)
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('field.jobs.claim', $job) }}">
                                @csrf
                                <button class="button primary" type="submit" @disabled($job->claimed_by !== null)>
                                    {{ $job->claimed_by ? 'Already Claimed' : 'Claim Job' }}
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $availableJobs->links() }}
                </div>
            @endif
        </section>

        <section class="section" aria-labelledby="my-jobs-title">
            <h2 id="my-jobs-title">My Jobs</h2>

            @if($myJobs->count() === 0)
                <div class="empty">You have not claimed any jobs yet.</div>
            @else
                <div class="list">
                    @foreach($myJobs as $job)
                        @php
                            $latestOwnAttempt = $job->attempts->first();
                            $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
                                ? \App\Models\JobItemAttempt::STATUS_REJECTED
                                : ($job->isOverdue() ? \App\Models\JobRequestItem::STATUS_OVERDUE : $job->status);
                        @endphp
                        <article class="item">
                            <div class="item-head">
                                <div>
                                    <div class="title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</div>
                                    <div class="muted">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</div>
                                </div>
                                <span class="status {{ $displayStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}</span>
                            </div>
                            <div class="meta">
                                <div>Client: {{ $job->jobRequest?->client?->client_name ?? '—' }}</div>
                                <div>Category: {{ $job->serviceCategory?->name ?? '—' }}</div>
                                <div>Claimed: {{ $job->claimed_at?->format('d M Y H:i') ?? '—' }}</div>
                                <div>
                                    Due:
                                    <span class="deadline {{ $job->isOverdue() ? 'overdue' : ($job->due_date?->isToday() ? 'today' : '') }}">
                                        {{ $job->due_date?->format('d M Y H:i') ?? '—' }}
                                        @if($job->isOverdue())
                                            (overdue)
                                        @elseif($job->due_date?->isToday())
                                            (due today)
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <a class="button secondary" href="{{ route('field.jobs.show', $job) }}">Open Job</a>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $myJobs->links() }}
                </div>
            @endif
        </section>
    </main>
</body>
</html>
