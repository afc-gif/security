@php
    $latestOwnAttempt = $jobItem->attempts->first();
    $isOverdue = $jobItem->isOverdue();
    $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
        ? \App\Models\JobItemAttempt::STATUS_REJECTED
        : ($isOverdue ? \App\Models\JobRequestItem::STATUS_OVERDUE : $jobItem->status);
    $adminNote = null;

    if (in_array($latestOwnAttempt?->status, [\App\Models\JobItemAttempt::STATUS_RETURNED, \App\Models\JobItemAttempt::STATUS_REJECTED], true)) {
        $notes = (string) $latestOwnAttempt->notes;
        $adminNote = str_contains($notes, 'Admin note:')
            ? trim(\Illuminate\Support\Str::afterLast($notes, 'Admin note:'))
            : trim($notes);
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="job-title">
            <p class="eyebrow">Job Details</p>
            <h1 id="job-title">{{ $jobItem->jobRequest?->title ?? 'Job request unavailable' }}</h1>
            <p class="subtext">{{ $jobItem->serviceCategory?->name ?? 'Service category' }}</p>
        </section>

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

        <section class="panel">
            <div class="grid">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $jobItem->jobRequest?->client?->client_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Category</div>
                    <div class="value">{{ $jobItem->serviceCategory?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $displayStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}</span>
                    </div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value deadline {{ $isOverdue ? 'overdue' : ($jobItem->due_date?->isToday() ? 'today' : '') }}">
                        {{ $jobItem->due_date?->format('d M Y H:i') ?? '-' }}
                        @if($isOverdue)
                            (overdue)
                        @elseif($jobItem->due_date?->isToday())
                            (due today)
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="panel" aria-labelledby="submission-title">
            <h2 id="submission-title">Job Report</h2>

            @if($isOverdue)
                <div class="notice error">Submission deadline exceeded. Contact admin.</div>
            @elseif(in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_CLAIMED, \App\Models\JobRequestItem::STATUS_RETURNED], true))
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
                    <div class="form-row">
                        <label for="notes">Report Notes</label>
                        <textarea id="notes" name="notes" required minlength="5">{{ old('notes') }}</textarea>
                    </div>
                    <button class="button full" type="submit">Submit Job Report</button>
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

    @include('field.partials.bottom-nav')
</body>
</html>
