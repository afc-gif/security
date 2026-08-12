@extends('admin.layout')

@section('title', 'Finance Overview | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <h1 class="finance-title">Welcome back, {{ auth()->user()?->name ?? 'Finance user' }}</h1>
                <p class="finance-subtitle">Track job expenses, manage project finances and keep everything under control.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="finance-stats">
            <div class="finance-stat">
                <div class="finance-stat-inner">
                    <span class="finance-stat-icon" aria-hidden="true">JB</span>
                    <div>
                        <div class="finance-stat-label">Active Jobs</div>
                        <div class="finance-stat-value">{{ $activeJobs }}</div>
                    </div>
                </div>
                <a href="{{ route('finance.jobs.index') }}" class="finance-stat-link">View all jobs <span aria-hidden="true">-></span></a>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-inner">
                    <span class="finance-stat-icon" aria-hidden="true">PJ</span>
                    <div>
                        <div class="finance-stat-label">Active Projects</div>
                        <div class="finance-stat-value">{{ $activeProjects }}</div>
                    </div>
                </div>
                <a href="{{ route('finance.projects.index') }}" class="finance-stat-link">View all projects <span aria-hidden="true">-></span></a>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-inner">
                    <span class="finance-stat-icon" aria-hidden="true">RV</span>
                    <div>
                        <div class="finance-stat-label">Pending Review</div>
                        <div class="finance-stat-value">{{ $pendingReviewCount }}</div>
                    </div>
                </div>
                <a href="{{ route('finance.jobs.index', ['status' => 'pending_assignment']) }}" class="finance-stat-link">View pending <span aria-hidden="true">-></span></a>
            </div>
        </div>

        <div class="finance-dashboard-grid">
            <section class="finance-panel">
                <div class="finance-section-head">
                    <div class="finance-section-title">Recent Jobs</div>
                    <a href="{{ route('finance.jobs.index') }}" class="finance-card-link">View all jobs <span aria-hidden="true">-></span></a>
                </div>

                @if($recentJobs->isEmpty())
                    <div class="px-5 py-10 text-center finance-muted">No jobs available yet.</div>
                @else
                    <div class="finance-list">
                        @foreach($recentJobs as $job)
                            @php($client = $job->jobRequest?->client)
                            <div class="finance-row finance-dashboard-row">
                                <span class="finance-stat-icon" aria-hidden="true">JB</span>
                                <div>
                                    <div class="finance-row-title">{{ $job->title ?: $job->jobRequest?->title ?: 'Untitled job' }}</div>
                                    <div class="finance-row-meta">Client: {{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                <a href="{{ route('finance.jobs.show', $job) }}" class="finance-btn finance-btn-primary">View</a>
                            </div>
                        @endforeach
                    </div>
                    <div class="finance-dashboard-footer">
                        <a href="{{ route('finance.jobs.index') }}" class="finance-card-link">View all jobs <span aria-hidden="true">-></span></a>
                    </div>
                @endif
            </section>

            <section class="finance-panel">
                <div class="finance-section-head">
                    <div class="finance-section-title">Recent Projects</div>
                    <a href="{{ route('finance.projects.index') }}" class="finance-card-link">View all projects <span aria-hidden="true">-></span></a>
                </div>

                @if($recentProjects->isEmpty())
                    <div class="px-5 py-10 text-center finance-muted">No projects available yet.</div>
                @else
                    <div class="finance-list">
                        @foreach($recentProjects as $project)
                            <div class="finance-row finance-dashboard-row">
                                <span class="finance-stat-icon" aria-hidden="true">PJ</span>
                                <div>
                                    <div class="finance-row-title">{{ $project->title ?: $project->project_code }}</div>
                                    <div class="finance-row-meta">Client: {{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                <a href="{{ route('finance.projects.show', $project) }}" class="finance-btn finance-btn-primary">View</a>
                            </div>
                        @endforeach
                    </div>
                    <div class="finance-dashboard-footer">
                        <a href="{{ route('finance.projects.index') }}" class="finance-card-link">View all projects <span aria-hidden="true">-></span></a>
                    </div>
                @endif
            </section>
        </div>

        <div class="finance-banner">
            <span class="finance-banner-icon" aria-hidden="true">FN</span>
            <div>
                <div class="finance-banner-title">Good finances build great projects.</div>
                <div class="finance-banner-text">Stay organized. Stay profitable.</div>
            </div>
        </div>
    </div>
</div>
@endsection
