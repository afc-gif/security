<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Jobs</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="jobs-title">
            <p class="eyebrow">Jobs</p>
            <h1 id="jobs-title">Field Jobs</h1>
            <p class="subtext">Claim available category items and continue active job reports.</p>
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

        <section class="section" aria-labelledby="available-jobs-title">
            <div class="section-heading">
                <h2 id="available-jobs-title">Available Jobs</h2>
            </div>

            @if($availableJobs->count() === 0)
                <div class="empty-state">No available jobs right now.</div>
            @else
                <div class="job-grid">
                    @foreach($availableJobs as $job)
                        <article class="job-card">
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge {{ $job->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($job->status)) }}</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</span>

                            <div class="job-meta">
                                <span>Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}</span>
                            </div>

                            <form method="POST" action="{{ route('field.jobs.claim', $job) }}">
                                @csrf
                                <button class="card-button" type="submit" @disabled($job->claimed_by !== null)>
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
            <div class="section-heading">
                <h2 id="my-jobs-title">My Jobs</h2>
            </div>

            @if($myJobs->count() === 0)
                <div class="empty-state">You have not claimed any jobs yet.</div>
            @else
                <div class="job-grid">
                    @foreach($myJobs as $job)
                        @php
                            $latestOwnAttempt = $job->attempts->first();
                            $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
                                ? \App\Models\JobItemAttempt::STATUS_REJECTED
                                : ($job->isOverdue() ? \App\Models\JobRequestItem::STATUS_OVERDUE : $job->status);
                            $isOverdue = $job->isOverdue();
                        @endphp
                        <article class="job-card">
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge {{ $displayStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</span>

                            <div class="meta">
                                <div>Claimed: {{ $job->claimed_at?->format('d M Y H:i') ?? '-' }}</div>
                                <div class="{{ $isOverdue ? 'due-overdue' : '' }}">
                                    Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}
                                    @if($isOverdue)
                                        (overdue)
                                    @elseif($job->due_date?->isToday())
                                        (due today)
                                    @endif
                                </div>
                            </div>

                            <a class="card-button secondary" href="{{ route('field.jobs.show', $job) }}">Open Job</a>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $myJobs->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
