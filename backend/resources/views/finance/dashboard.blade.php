@extends('admin.layout')

@section('title', 'Finance Overview | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-950">Finance Overview</h1>
                <p class="mt-1 text-sm text-gray-600">Open Jobs to record job expenses. Open Projects to review project finance.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <a href="{{ route('finance.jobs.index') }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-blue-500 hover:shadow-md">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-blue-700">Start Here</div>
                        <h2 class="mt-2 text-2xl font-extrabold text-gray-950">Jobs</h2>
                        <p class="mt-2 max-w-xl text-sm text-gray-600">Find an existing job, open it, and add expenses directly to that job.</p>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-right">
                        <div class="text-2xl font-extrabold text-gray-950">{{ $jobCount }}</div>
                        <div class="text-xs font-bold uppercase tracking-wide text-blue-700">Jobs</div>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-md bg-gray-50 px-4 py-3">
                        <div class="text-sm font-bold text-gray-600">Approved Job Expenses</div>
                        <div class="mt-1 text-xl font-extrabold text-gray-950">{{ $financeMoney($jobApprovedExpenseTotal) }}</div>
                    </div>
                    <div class="rounded-md bg-gray-50 px-4 py-3">
                        <div class="text-sm font-bold text-gray-600">Expense Records</div>
                        <div class="mt-1 text-xl font-extrabold text-gray-950">{{ $jobExpenseCount }}</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('finance.projects.index') }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-emerald-500 hover:shadow-md">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Project Finance</div>
                        <h2 class="mt-2 text-2xl font-extrabold text-gray-950">Projects</h2>
                        <p class="mt-2 max-w-xl text-sm text-gray-600">Review project budgets, approved costs, materials, and private receipts.</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-right">
                        <div class="text-2xl font-extrabold text-gray-950">{{ $projectReviewCount }}</div>
                        <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">Need Review</div>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-md bg-gray-50 px-4 py-3">
                        <div class="text-sm font-bold text-gray-600">Approved Project Costs</div>
                        <div class="mt-1 text-xl font-extrabold text-gray-950">{{ $financeMoney($totalApprovedProjectCosts) }}</div>
                    </div>
                    <div class="rounded-md bg-gray-50 px-4 py-3">
                        <div class="text-sm font-bold text-gray-600">Estimated Profit</div>
                        <div class="mt-1 text-xl font-extrabold {{ $estimatedProjectProfit < 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $financeMoney($estimatedProjectProfit) }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-bold text-gray-600">Pending Job/Inspection Expenses</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-950">{{ $pendingCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingTotal) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-bold text-gray-600">Pending Project Expenses</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-950">{{ $pendingProjectExpenseCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingProjectExpenseTotal) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-bold text-gray-600">Pending Material Costs</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-950">{{ $pendingMaterialCostCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingMaterialCostTotal) }}</div>
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Recent Job Expenses</h2>
                    <p class="mt-1 text-sm text-gray-600">Latest expenses recorded before project conversion.</p>
                </div>
                <a href="{{ route('finance.jobs.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">Open Jobs</a>
            </div>
            @php
                $jobExpenses = $recentExpenses->filter(fn ($expense) => $expense->job_request_item_id !== null)->values();
            @endphp

            @if($jobExpenses->isEmpty())
                <div class="p-8 text-center text-gray-600">No job expenses recorded yet.</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($jobExpenses as $expense)
                        @continue(!$expense->jobRequestItem)
                        <a href="{{ route('finance.jobs.show', $expense->jobRequestItem) }}" class="block px-5 py-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="truncate font-extrabold text-gray-950">{{ $expense->jobRequestItem?->title ?? 'Job unavailable' }}</div>
                                    <div class="mt-1 truncate text-sm text-gray-600">{{ $expense->jobRequestItem?->jobRequest?->client?->company_name ?: $expense->jobRequestItem?->jobRequest?->client?->client_name ?: 'Client unavailable' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $expense->category?->name ?? 'Expense' }} / {{ $financeStatusLabel($expense->status) }}</div>
                                </div>
                                <div class="shrink-0 text-right font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
