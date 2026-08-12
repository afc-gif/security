@extends('admin.layout')

@section('title', 'Finance Jobs | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <h1 class="finance-title">Jobs</h1>
                <p class="finance-subtitle">Select a job to record expenses and track spending.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.jobs.index') }}" class="finance-filter-bar">
            <div class="finance-filter-content">
                <input 
                    type="search" 
                    name="search" 
                    value="{{ $filters['search'] ?? '' }}" 
                    placeholder="Search by job name, client, or staff..."
                    class="finance-filter-input"
                >
                <select name="status" class="finance-filter-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="finance-btn finance-btn-primary">Search</button>
                @if(($filters['search'] ?? null) || ($filters['status'] ?? null))
                    <a href="{{ route('finance.jobs.index') }}" class="finance-btn finance-btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if($jobs->isEmpty())
            <div class="finance-empty-message">
                <div class="finance-empty-icon">📋</div>
                <div class="finance-empty-title">No jobs found</div>
                <div class="finance-empty-text">No jobs match your search. Try adjusting your filters.</div>
            </div>
        @else
            <div class="finance-jobs-list">
                @foreach($jobs as $job)
                    @php
                        $client = $job->jobRequest?->client;
                        $jobTitle = $job->title ?: $job->jobRequest?->title ?: 'Untitled job';
                        $clientName = $client?->company_name ?: $client?->client_name ?: 'Client unavailable';
                        $staffName = $job->claimer?->name ?? 'Unassigned';
                        $totalSpent = $job->expenses_total ?? 0;
                    @endphp
                    <a href="{{ route('finance.jobs.show', $job) }}" class="finance-job-card">
                        <div class="finance-job-header">
                            <div class="finance-job-title">{{ $jobTitle }}</div>
                            <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                        </div>
                        <div class="finance-job-info">
                            <div class="finance-job-detail">
                                <span class="finance-job-label">Client</span>
                                <span class="finance-job-value">{{ $clientName }}</span>
                            </div>
                            <div class="finance-job-detail">
                                <span class="finance-job-label">Staff</span>
                                <span class="finance-job-value">{{ $staffName }}</span>
                            </div>
                            <div class="finance-job-detail finance-job-detail-amount">
                                <span class="finance-job-label">Total Spent</span>
                                <span class="finance-job-value-amount">{{ $financeMoney($totalSpent) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="finance-pagination">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
