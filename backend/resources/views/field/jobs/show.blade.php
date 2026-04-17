<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
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
        .page { width: min(760px, 100%); margin: 0 auto; padding: 20px 14px; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        h1 { margin: 0; font-size: 28px; line-height: 1.2; }
        .link, .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-weight: 700; text-decoration: none; }
        .button.primary { width: 100%; min-height: 48px; border-color: var(--action); background: var(--action); color: #fff; cursor: pointer; }
        .button.primary:hover { border-color: var(--action-dark); background: var(--action-dark); }
        .panel { padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); margin-bottom: 12px; }
        .muted { color: var(--muted); font-size: 14px; }
        .meta { display: grid; gap: 10px; margin-top: 14px; }
        .label { color: var(--muted); font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0; }
        .value { margin-top: 2px; font-weight: 700; }
        .status { display: inline-flex; padding: 4px 8px; border-radius: 8px; background: #e5e7eb; color: #374151; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .status.open { background: #e0f2fe; color: #075985; }
        .status.claimed { background: #dbeafe; color: #1d4ed8; }
        .status.submitted { background: #fef3c7; color: #92400e; }
        .status.returned { background: #ffedd5; color: #9a3412; }
        .status.approved { background: #dcfce7; color: #166534; }
        .status.reopened { background: #e0f2fe; color: #075985; }
        .status.rejected { background: #fee2e2; color: #991b1b; }
        textarea { width: 100%; min-height: 140px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font: inherit; resize: vertical; }
        .notice { padding: 14px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); color: var(--muted); margin-bottom: 12px; }
        .success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .error { border-color: #fecaca; background: #fef2f2; color: var(--danger); }
        .locked { border-color: #fde68a; background: #fffbeb; color: #92400e; }
        .admin-note { margin-top: 10px; padding: 12px; border-radius: 8px; border: 1px solid #fed7aa; background: #fff7ed; color: #9a3412; white-space: pre-line; }

        @media (max-width: 640px) {
            .link, .button { width: 100%; }
            .nav { display: grid; }
        }
    </style>
</head>
<body>
    <main class="page">
        <nav class="nav" aria-label="Field navigation">
            <a class="link" href="{{ route('field.jobs.index') }}">Back to Field Jobs</a>
            <a class="link" href="{{ route('field.dashboard') }}">My Dashboard</a>
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

        @php
            $latestOwnAttempt = $jobItem->attempts->first();
            $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
                ? \App\Models\JobItemAttempt::STATUS_REJECTED
                : $jobItem->status;
            $adminNote = null;

            if (in_array($latestOwnAttempt?->status, [\App\Models\JobItemAttempt::STATUS_RETURNED, \App\Models\JobItemAttempt::STATUS_REJECTED], true)) {
                $notes = (string) $latestOwnAttempt->notes;
                $adminNote = str_contains($notes, 'Admin note:')
                    ? trim(\Illuminate\Support\Str::afterLast($notes, 'Admin note:'))
                    : trim($notes);
            }
        @endphp

        <section class="panel" aria-labelledby="job-title">
            <div class="muted">{{ $jobItem->serviceCategory?->name ?? 'Service category' }}</div>
            <h1 id="job-title">{{ $jobItem->jobRequest?->title ?? 'Job request unavailable' }}</h1>

            <div class="meta">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $jobItem->jobRequest?->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Category</div>
                    <div class="value">{{ $jobItem->serviceCategory?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $displayStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel" aria-labelledby="submission-title">
            <h2 id="submission-title">Job Report</h2>

            @if(in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_CLAIMED, \App\Models\JobRequestItem::STATUS_RETURNED], true))
                @if($jobItem->status === \App\Models\JobRequestItem::STATUS_RETURNED)
                    <div class="notice locked">
                        This job was returned for updates. Submit the corrected report when ready.
                        @if($adminNote)
                            <div class="admin-note"><strong>Admin note:</strong><br>{{ $adminNote }}</div>
                        @endif
                    </div>
                @endif
                <form method="POST" action="{{ route('field.jobs.submit', $jobItem) }}">
                    @csrf
                    <label class="label" for="notes">Report Notes</label>
                    <textarea id="notes" name="notes" required minlength="5">{{ old('notes') }}</textarea>
                    <button class="button primary" type="submit">Submit Job Report</button>
                </form>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_SUBMITTED)
                <div class="notice locked">Submitted. Awaiting review.</div>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_APPROVED)
                <div class="notice success">Approved</div>
            @elseif($displayStatus === \App\Models\JobItemAttempt::STATUS_REJECTED)
                <div class="notice error">
                    Rejected
                    @if($adminNote)
                        <div class="admin-note"><strong>Admin note:</strong><br>{{ $adminNote }}</div>
                    @endif
                </div>
            @else
                <div class="notice">This job is not available for submission.</div>
            @endif
        </section>
    </main>
</body>
</html>
