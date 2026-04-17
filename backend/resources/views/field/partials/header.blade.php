@php
    $fieldUser = auth()->user();
    $fieldFirstName = trim(explode(' ', $fieldUser?->name ?? 'Field Staff')[0]);
    $availableJobsCount = $availableJobsCount ?? 0;
    $myJobsCount = $myJobsCount ?? $myClaimedJobs ?? 0;
    $overdueJobsCount = $overdueJobsCount ?? $overdueJobs ?? 0;
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
            <strong>{{ $myJobsCount }}</strong>
            <span>My Jobs</span>
        </div>
        <div class="status-tile danger">
            <strong>{{ $overdueJobsCount }}</strong>
            <span>Overdue</span>
        </div>
    </div>
</header>
