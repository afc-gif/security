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

        <div class="finance-section-container">
            <div class="finance-section-header">
                <h2 class="finance-section-title">Recent Activity</h2>
                <a href="{{ route('finance.jobs.index') }}" class="finance-card-link">View all <span aria-hidden="true">-></span></a>
            </div>

            <div class="finance-overview-cards">
                @if($recentJobs->isEmpty() && $recentProjects->isEmpty())
                    <div class="finance-empty-state">
                        <div class="finance-empty-icon">📊</div>
                        <div class="finance-empty-title">No activity yet</div>
                        <div class="finance-empty-text">Create or assign jobs and projects to see them here</div>
                    </div>
                @else
                    @if(!$recentJobs->isEmpty())
                        <div class="finance-overview-subsection">
                            <div class="finance-subsection-header">
                                <h3 class="finance-subsection-title">Jobs</h3>
                                <span class="finance-count-badge">{{ $recentJobs->count() }}</span>
                            </div>
                            <div class="finance-card-grid">
                                @foreach($recentJobs->take(3) as $job)
                                    @php($client = $job->jobRequest?->client)
                                    <a href="{{ route('finance.jobs.show', $job) }}" class="finance-overview-card">
                                        <div class="finance-card-icon">JB</div>
                                        <div class="finance-card-content">
                                            <div class="finance-card-title">{{ $job->title ?: $job->jobRequest?->title ?: 'Untitled job' }}</div>
                                            <div class="finance-card-client">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                        </div>
                                        <span class="finance-card-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!$recentProjects->isEmpty())
                        <div class="finance-overview-subsection">
                            <div class="finance-subsection-header">
                                <h3 class="finance-subsection-title">Projects</h3>
                                <span class="finance-count-badge">{{ $recentProjects->count() }}</span>
                            </div>
                            <div class="finance-card-grid">
                                @foreach($recentProjects->take(3) as $project)
                                    <a href="{{ route('finance.projects.show', $project) }}" class="finance-overview-card">
                                        <div class="finance-card-icon">PJ</div>
                                        <div class="finance-card-content">
                                            <div class="finance-card-title">{{ $project->title ?: $project->project_code }}</div>
                                            <div class="finance-card-client">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                                        </div>
                                        <span class="finance-card-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
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
