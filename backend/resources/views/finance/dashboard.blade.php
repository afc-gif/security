@extends('admin.layout')

@section('title', 'Finance Overview | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <div class="finance-eyebrow">Finance</div>
                <h1 class="finance-title">Track job expenses and project finances.</h1>
                <p class="finance-subtitle">A simple workspace for daily finance records.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="finance-stats">
            <div class="finance-stat">
                <div class="finance-stat-label">Active Jobs</div>
                <div class="finance-stat-value">{{ $activeJobs }}</div>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-label">Active Projects</div>
                <div class="finance-stat-value">{{ $activeProjects }}</div>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-label">Pending Review</div>
                <div class="finance-stat-value">{{ $pendingReviewCount }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section class="finance-panel">
                <div class="finance-section-head">
                    <div class="finance-section-title">Recent Jobs</div>
                    <a href="{{ route('finance.jobs.index') }}" class="finance-btn finance-btn-secondary">View All Jobs</a>
                </div>

                @if($recentJobs->isEmpty())
                    <div class="px-5 py-10 text-center finance-muted">No jobs available yet.</div>
                @else
                    <div class="finance-list">
                        @foreach($recentJobs as $job)
                            @php($client = $job->jobRequest?->client)
                            <div class="finance-row">
                                <div>
                                    <div class="finance-row-title">{{ $job->title ?: $job->jobRequest?->title ?: 'Untitled job' }}</div>
                                    <div class="finance-row-meta">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <div></div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                <a href="{{ route('finance.jobs.show', $job) }}" class="finance-btn finance-btn-primary">View</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="finance-panel">
                <div class="finance-section-head">
                    <div class="finance-section-title">Recent Projects</div>
                    <a href="{{ route('finance.projects.index') }}" class="finance-btn finance-btn-secondary">View All Projects</a>
                </div>

                @if($recentProjects->isEmpty())
                    <div class="px-5 py-10 text-center finance-muted">No projects available yet.</div>
                @else
                    <div class="finance-list">
                        @foreach($recentProjects as $project)
                            <div class="finance-row">
                                <div>
                                    <div class="finance-row-title">{{ $project->title ?: $project->project_code }}</div>
                                    <div class="finance-row-meta">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <div></div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                <a href="{{ route('finance.projects.show', $project) }}" class="finance-btn finance-btn-primary">View</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
