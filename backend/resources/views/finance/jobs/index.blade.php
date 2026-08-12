@extends('admin.layout')

@section('title', 'Finance Jobs | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header">
            <div>
                <div class="finance-eyebrow">Jobs</div>
                <h1 class="finance-title">Find a job and record its expenses.</h1>
                <p class="finance-subtitle">Search by job title, client, company, or assigned work.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.jobs.index') }}" class="finance-panel-flat finance-filter">
            <div class="finance-field">
                <label for="search">Search jobs</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Search jobs...">
            </div>
            <div class="finance-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="finance-btn finance-btn-primary">Search</button>
                @if(($filters['search'] ?? null) || ($filters['status'] ?? null))
                    <a href="{{ route('finance.jobs.index') }}" class="finance-btn finance-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <section class="finance-panel">
            <div class="finance-section-head">
                <div>
                    <div class="finance-section-title">Jobs</div>
                    <div class="finance-row-meta">{{ $jobs->total() }} {{ Illuminate\Support\Str::plural('job', $jobs->total()) }} found</div>
                </div>
            </div>

            @if($jobs->isEmpty())
                <div class="px-5 py-12 text-center finance-muted">No jobs found.</div>
            @else
                <div class="finance-list">
                    @foreach($jobs as $job)
                        @php
                            $client = $job->jobRequest?->client;
                            $lastExpense = $job->financialExpenses()->latest('incurred_on')->latest('created_at')->first();
                        @endphp
                        <div class="finance-row">
                            <div>
                                <div class="finance-row-title">{{ $job->title ?: $job->jobRequest?->title }}</div>
                                <div class="finance-row-meta">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                            </div>
                            <div>
                                <div class="finance-row-meta">Assigned staff</div>
                                <div class="font-bold text-gray-800">{{ $job->claimer?->name ?? 'Unassigned' }}</div>
                            </div>
                            <div>
                                <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $lastExpense?->incurred_on?->format('M j, Y') ?? $lastExpense?->created_at?->format('M j, Y') ?? 'No expenses' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-gray-950">{{ $financeMoney($job->approved_expenses_total ?? 0) }}</div>
                                <a href="{{ route('finance.jobs.show', $job) }}" class="finance-btn finance-btn-primary mt-2">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="mt-5">
            {{ $jobs->links() }}
        </div>
    </div>
</div>
@endsection
