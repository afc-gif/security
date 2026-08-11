@extends('admin.layout')

@section('title', 'Finance | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-950">Finance Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">Start here to record money spent, review pending records, and track project profitability.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('finance.projects.index') }}" class="inline-flex items-center justify-center bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                    Project Finance
                </a>
                @can(\App\Models\FinancePermission::CREATE)
                    <a href="{{ route('finance.expenses.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                        Record Expense
                    </a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-950">Start Here</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            @can(\App\Models\FinancePermission::CREATE)
                <a href="{{ route('finance.expenses.create', ['context_type' => 'inspection']) }}" class="group rounded-lg border border-blue-200 bg-white p-5 shadow-sm transition hover:border-blue-500 hover:shadow-md">
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-sm font-extrabold text-blue-700">1</div>
                    <div class="mt-4 text-lg font-extrabold text-gray-950">Record Transportation or Other Pre-Project Expense</div>
                    <div class="mt-2 text-sm text-gray-600">Use this for inspection/job spending before a project exists.</div>
                </a>
            @endcan
            <a href="{{ route('finance.projects.index') }}" class="group rounded-lg border border-emerald-200 bg-white p-5 shadow-sm transition hover:border-emerald-500 hover:shadow-md">
                <div class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-emerald-50 text-sm font-extrabold text-emerald-700">2</div>
                <div class="mt-4 text-lg font-extrabold text-gray-950">Open Project Finance</div>
                <div class="mt-2 text-sm text-gray-600">Set contract value, budget, material cost, documents, and profit tracking.</div>
            </a>
            @can(\App\Models\FinancePermission::APPROVE)
                <a href="{{ route('finance.expenses.index', ['status' => \App\Models\FinancialExpense::STATUS_PENDING]) }}" class="group rounded-lg border border-amber-200 bg-white p-5 shadow-sm transition hover:border-amber-500 hover:shadow-md">
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-amber-50 text-sm font-extrabold text-amber-700">3</div>
                    <div class="mt-4 text-lg font-extrabold text-gray-950">Approve Pending Records</div>
                    <div class="mt-2 text-sm text-gray-600">{{ $pendingCount + $pendingProjectExpenseCount }} expense records and {{ $pendingMaterialCostCount }} material costs pending.</div>
                </a>
            @endcan
        </div>

        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-950">Project Money</h2>
            <a href="{{ route('finance.projects.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">View all projects</a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Project Contract Value</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $financeMoney($totalContractValue) }}</div>
                <div class="mt-1 text-sm text-gray-600">Finance profiles only</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Project Budget</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $financeMoney($totalApprovedBudget) }}</div>
                <div class="mt-1 text-sm text-gray-600">Across project finance profiles</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Project Costs</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $financeMoney($totalApprovedProjectCosts) }}</div>
                <div class="mt-1 text-sm text-gray-600">Expenses {{ $financeMoney($totalApprovedProjectExpenses) }} · Materials {{ $financeMoney($totalApprovedMaterialCosts) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estimated Profit</div>
                <div class="mt-2 text-2xl font-bold {{ $estimatedProjectProfit < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $financeMoney($estimatedProjectProfit) }}</div>
                <div class="mt-1 text-sm text-gray-600">Contract value minus approved costs</div>
            </div>
        </div>

        <div class="mb-4 mt-8 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-950">Review Queue</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Over Budget Projects</div>
                <div class="mt-2 text-2xl font-bold {{ $overBudgetProjectCount > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $overBudgetProjectCount }}</div>
                <div class="mt-1 text-sm text-gray-600">Approved costs exceed budget</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Projects Awaiting Review</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $projectReviewCount }}</div>
                <div class="mt-1 text-sm text-gray-600">Missing profiles or pending costs</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Project Expenses</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingProjectExpenseCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingProjectExpenseTotal) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Material Costs</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingMaterialCostCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingMaterialCostTotal) }}</div>
            </div>
        </div>

        <div class="mb-4 mt-8 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-950">Pre-Project Records</h2>
            <a href="{{ route('finance.expenses.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">View expense list</a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Pre-Project Expenses</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingTotal) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Pre-Project</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $approvedCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($approvedTotal) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rejected Expenses</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $rejectedCount }}</div>
                <div class="mt-1 text-sm text-gray-600">Retained in history</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Transportation</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $transportCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($transportTotal) }}</div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-gray-950">Recent Pre-Project Activity</h2>
                    <a href="{{ route('finance.expenses.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">View all</a>
                </div>
                @if($recentExpenses->isEmpty())
                    <div class="p-8 text-center text-gray-600">No pre-project expenses recorded yet.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentExpenses as $expense)
                            <a href="{{ route('finance.expenses.show', $expense) }}" class="block px-5 py-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="truncate font-bold text-gray-950">{{ $financeContextReference($expense) }}</div>
                                        <div class="truncate text-sm text-gray-600">{{ $financeContextTitle($expense) }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $expense->category?->name ?? '—' }} · {{ $financeContextClient($expense) }}</div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                                        <span class="mt-1 inline-flex items-center rounded px-2 py-1 text-xs font-bold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-gray-950">Pending Material Review</h2>
                    <a href="{{ route('finance.projects.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">View projects</a>
                </div>
                @if($recentMaterialCosts->isEmpty())
                    <div class="p-8 text-center text-gray-600">No pending material costs require review.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentMaterialCosts as $materialCost)
                            <a href="{{ route('finance.material-costs.show', $materialCost) }}" class="block px-5 py-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="truncate font-bold text-gray-950">{{ $materialCost->material_name }}</div>
                                        <div class="truncate text-sm text-gray-600">{{ $materialCost->project?->project_code ?? 'Project unavailable' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Submitted by {{ $materialCost->submitter?->name ?? '—' }}</div>
                                    </div>
                                    <div class="shrink-0 text-right font-extrabold text-gray-950">{{ $financeMoney($materialCost->total_cost) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
