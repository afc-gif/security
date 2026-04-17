<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inspections</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="inspections-title">
            <p class="eyebrow">Inspections</p>
            <h1 id="inspections-title">My Inspections</h1>
            <p class="subtext">Open assigned site inspections and submit field reports.</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        <section class="section">
            @if($inspections->count() === 0)
                <div class="empty-state">No inspections assigned yet.</div>
            @else
                <div class="card-grid">
                    @foreach($inspections as $inspection)
                        <article class="job-card">
                            <div class="card-head">
                                <div>
                                    <h3 class="card-title">{{ $inspection->inspection_code }}</h3>
                                    <p class="card-subtitle">{{ $inspection->title }}</p>
                                </div>
                                <span class="status {{ $inspection->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($inspection->status)) }}</span>
                            </div>

                            <div class="meta">
                                <div>Client: {{ $inspection->client?->client_name ?? '-' }}</div>
                                <div>Date: {{ $inspection->scheduled_date?->format('d M Y H:i') ?? '-' }}</div>
                            </div>

                            <a class="card-button" href="{{ route('field.inspections.show', $inspection) }}">Open Inspection</a>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $inspections->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
