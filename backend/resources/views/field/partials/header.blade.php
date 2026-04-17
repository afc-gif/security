@php
    $fieldUser = auth()->user();
    $fieldUserId = $fieldUser?->id;
    $fieldFirstName = trim(explode(' ', $fieldUser?->name ?? 'Field Staff')[0]);
    $availableJobsCount = $availableJobsCount ?? \App\Models\JobRequestItem::query()
        ->available()
        ->when($fieldUserId, function ($query) use ($fieldUserId) {
            $query->whereNotExists(function ($attemptQuery) use ($fieldUserId) {
                $attemptQuery->selectRaw('1')
                    ->from('job_item_attempts')
                    ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                    ->where('job_item_attempts.user_id', $fieldUserId)
                    ->where('job_item_attempts.status', \App\Models\JobItemAttempt::STATUS_REJECTED);
            });
        })
        ->count();
    $myClaimedJobs = $myClaimedJobs ?? \App\Models\JobRequestItem::where('claimed_by', $fieldUserId)
        ->where('status', \App\Models\JobRequestItem::STATUS_CLAIMED)
        ->count();
    $overdueJobs = $overdueJobs ?? \App\Models\JobRequestItem::where('claimed_by', $fieldUserId)
        ->where(function ($query) {
            $query->where('status', \App\Models\JobRequestItem::STATUS_OVERDUE)
                ->orWhere(function ($overdueQuery) {
                    $overdueQuery->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->whereIn('status', [
                            \App\Models\JobRequestItem::STATUS_CLAIMED,
                            \App\Models\JobRequestItem::STATUS_RETURNED,
                        ]);
                });
        })
        ->count();
@endphp

<header class="app-header">
    <div class="header-row">
        <div class="brand-lockup">
            <div class="logo-frame">
                <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI logo">
            </div>
            <div>
                <p class="app-name">ARTSCI Field</p>
                <p class="hello">Hi, {{ $fieldFirstName }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button" type="submit">Logout</button>
        </form>
    </div>

    <div class="status-strip" aria-label="Field status summary">
        <div class="status-tile">
            <strong>{{ $availableJobsCount ?? 0 }}</strong>
            <span>Available Jobs</span>
        </div>
        <div class="status-tile">
            <strong>{{ $myClaimedJobs ?? 0 }}</strong>
            <span>My Jobs</span>
        </div>
        <div class="status-tile danger">
            <strong>{{ $overdueJobs ?? 0 }}</strong>
            <span>Overdue</span>
        </div>
    </div>
</header>
