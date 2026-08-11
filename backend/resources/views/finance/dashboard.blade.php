@extends('admin.layout')

@section('title', 'Finance | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Finance</h1>
                <p class="text-sm text-gray-600 mt-1">Private finance overview for pre-project expenses, project budgets, and approval work.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
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

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900">Recent Financial Activity</h2>
                <a href="{{ route('finance.expenses.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View all</a>
            </div>
            @if($recentExpenses->isEmpty())
                <div class="p-8 text-center text-gray-600">No pre-project expenses recorded yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Context</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentExpenses as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('finance.expenses.show', $expense) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $financeContextReference($expense) }}</a>
                                        <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $financeContextTitle($expense) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $financeContextClient($expense) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($expense->amount) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900">Pending Material Review</h2>
                <a href="{{ route('finance.projects.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View projects</a>
            </div>
            @if($recentMaterialCosts->isEmpty())
                <div class="p-8 text-center text-gray-600">No pending material costs require review.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Project</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Material</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Total Cost</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Submitted By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentMaterialCosts as $materialCost)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('finance.material-costs.show', $materialCost) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $materialCost->project?->project_code ?? 'Project unavailable' }}</a>
                                        <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $materialCost->project?->client?->client_name ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $materialCost->material_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($materialCost->total_cost) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $materialCost->submitter?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
