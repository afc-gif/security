<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Assignment</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="assignment-title">
            <p class="eyebrow">Coordinator</p>
            <h1 id="assignment-title">Job Assignment</h1>
            <p class="subtext">Assign new job requests to field staff or release them for open claim.</p>
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

        <section class="section">
            @if($pendingJobs->count() === 0)
                <div class="empty-state">No jobs are waiting for assignment.</div>
            @else
                <div class="job-grid">
                    @foreach($pendingJobs as $job)
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

                            <form method="POST" action="{{ route('coordinator.jobs.assign', $job) }}" class="form-row">
                                @csrf
                                <label for="assigned_to_{{ $job->id }}">Assign to</label>
                                <select id="assigned_to_{{ $job->id }}" name="assigned_to" required>
                                    <option value="">Select staff</option>
                                    @foreach($fieldStaff as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }}{{ $staff->role === 'field_coordinator' ? ' (Coordinator)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="card-button" type="submit">Assign Job</button>
                            </form>

                            <form method="POST" action="{{ route('coordinator.jobs.claim', $job) }}" style="margin-top:10px;">
                                @csrf
                                <button class="card-button secondary" type="submit">Assign to Me</button>
                            </form>

                            <form method="POST" action="{{ route('coordinator.jobs.release', $job) }}" style="margin-top:10px;">
                                @csrf
                                <button class="card-button secondary" type="submit">Release for Claim</button>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $pendingJobs->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
