@extends('admin.layout')

@section('title', 'Finance Projects | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <div class="finance-eyebrow">Projects</div>
                <h1 class="finance-title">Project Financial Overview</h1>
                <p class="finance-subtitle">Manage project values, payments, and expenses.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="finance-success-alert">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('finance.projects.index') }}" class="finance-filter-bar">
            <div class="finance-filter-content">
                <input type="search" name="search" placeholder="Search projects..." value="{{ $filters['search'] ?? '' }}" class="finance-filter-input">
                <select name="status" class="finance-filter-select">
                    <option value="">All statuses</option>
                    @foreach(['not_started', 'ongoing', 'on_hold', 'ready_for_review', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="finance-btn finance-btn-primary">Filter</button>
            </div>
        </form>

        @if($projects->isEmpty())
            <div class="finance-empty-message">
                <div class="finance-empty-icon">📋</div>
                <div class="finance-empty-title">No projects found</div>
                <div class="finance-empty-text">There are currently no projects to display.</div>
            </div>
        @else
            <div class="finance-jobs-list">
                @foreach($projects as $project)
                    @php
                        $summary = $summaries[$project->id];
                        $balanceColor = !empty($summary['is_overpaid']) ? '#059669' : (($summary['balance_due'] ?? 0) > 0 ? '#f59e0b' : '#059669');
                    @endphp
                    <a href="{{ route('finance.projects.show', $project) }}" class="finance-job-card">
                        <div class="finance-job-header">
                            <h3 class="finance-job-title">{{ $project->title ?: $project->project_code }}</h3>
                        </div>
                        <div class="finance-job-info">
                            <div class="finance-job-detail">
                                <div class="finance-job-label">Client</div>
                                <div class="finance-job-value">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'N/A' }}</div>
                            </div>
                            <div class="finance-job-detail">
                                <div class="finance-job-label">Project Value</div>
                                <div class="finance-job-value-amount">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
                            </div>
                            <div class="finance-job-detail finance-job-detail-amount">
                                <div class="finance-job-label">Balance Due</div>
                                <div class="finance-job-value-amount @if(!empty($summary['is_overpaid'])) text-red-600 @elseif(($summary['balance_due'] ?? 0) > 0) text-amber-600 @endif">
                                    @if($summary['contract_value'] === null)
                                        -
                                    @elseif(!empty($summary['is_overpaid']))
                                        Overpaid ({{ $financeMoney($summary['overpaid_amount']) }})
                                    @else
                                        {{ $financeMoney($summary['balance_due']) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                            <div style="display: flex; gap: 6px;">
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                <span class="finance-status" style="background-color: #f0eaff; color: #4f24e8;">Spent: {{ $financeMoney($summary['approved_cost']) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="finance-pagination">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

