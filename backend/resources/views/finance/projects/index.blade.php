@extends('admin.layout')

@section('title', 'Finance Projects | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <div class="finance-eyebrow">Projects</div>
                <h1 class="finance-title">Review project value, costs, and profit.</h1>
                <p class="finance-subtitle">Open a project to manage its financial workspace.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.projects.index') }}" class="finance-panel-flat finance-filter finance-filter-compact">
            <div class="finance-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['not_started', 'ongoing', 'on_hold', 'ready_for_review', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="finance-btn finance-btn-primary">Apply</button>
                @if($filters['status'] ?? null)
                    <a href="{{ route('finance.projects.index') }}" class="finance-btn finance-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <section class="finance-panel">
            <div class="finance-section-head">
                <div>
                    <div class="finance-section-title">Projects</div>
                    <div class="finance-row-meta">{{ $projects->total() }} {{ Illuminate\Support\Str::plural('project', $projects->total()) }} found</div>
                </div>
            </div>

            @if($projects->isEmpty())
                <div class="px-5 py-12 text-center finance-muted">No projects found.</div>
            @else
                <div class="finance-list">
                    @foreach($projects as $project)
                        @php($summary = $summaries[$project->id])
                        <div class="finance-row">
                            <div>
                                <div class="finance-row-title">{{ $project->title ?: $project->project_code }}</div>
                                <div class="finance-row-meta">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                            </div>
                            <div>
                                <div class="finance-row-meta">Project value</div>
                                <div class="font-extrabold text-gray-950">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
                            </div>
                            <div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">Costs {{ $financeMoney($summary['approved_cost']) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-950' }}">
                                    {{ $summary['estimated_profit'] === null ? '-' : $financeMoney($summary['estimated_profit']) }}
                                </div>
                                <a href="{{ route('finance.projects.show', $project) }}" class="finance-btn finance-btn-primary mt-2">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="mt-5">
            {{ $projects->links() }}
        </div>
    </div>
</div>
@endsection
