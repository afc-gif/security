@extends('admin.layout')

@section('title', 'Project Financial Report | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-4">
            <div>
                <a href="{{ route('finance.reports.index') }}" class="text-xs font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1 mb-2 no-print">
                    &larr; Back to Reports
                </a>
                <h1 class="finance-title">Project Financial Report</h1>
                <p class="finance-subtitle">Project-level contract values, payments received, approved costs, and profit performance.</p>
            </div>
            <div class="flex items-center gap-2 mt-3 sm:mt-0 no-print">
                <button type="button" onclick="window.print()" class="finance-btn finance-btn-secondary px-3 py-1.5 text-xs">
                    🖨️ Print
                </button>
                <a href="{{ route('finance.reports.projects.export', request()->query()) }}" class="finance-btn finance-btn-primary px-3 py-1.5 text-xs">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Filter-Aware Totals Summary -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Value</div>
                <div class="text-lg md:text-xl font-bold text-slate-900 tabular-nums mt-0.5">{{ $financeMoney($totals['project_value']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                <div class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Total Received</div>
                <div class="text-lg md:text-xl font-bold text-emerald-700 tabular-nums mt-0.5">{{ $financeMoney($totals['received']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                <div class="text-[11px] font-medium text-amber-600 uppercase tracking-wider">Total Outstanding</div>
                <div class="text-lg md:text-xl font-bold text-amber-700 tabular-nums mt-0.5">{{ $financeMoney($totals['outstanding']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                <div class="text-[11px] font-medium text-rose-600 uppercase tracking-wider">Approved Costs</div>
                <div class="text-lg md:text-xl font-bold text-rose-700 tabular-nums mt-0.5">{{ $financeMoney($totals['approved_costs']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm col-span-2 lg:col-span-1">
                <div class="text-[11px] font-medium text-sky-600 uppercase tracking-wider">Estimated Profit</div>
                <div class="text-lg md:text-xl font-bold text-sky-700 tabular-nums mt-0.5">{{ $financeMoney($totals['estimated_profit']) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('finance.reports.projects') }}" class="bg-white rounded-xl border border-slate-200 p-4 mb-6 shadow-sm no-print">
            <div class="grid sm:grid-cols-3 gap-3 items-end">
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Search</label>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Project or client name..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Statuses</option>
                        <option value="not_started" @selected(($filters['status'] ?? '') === 'not_started')>Not Started</option>
                        <option value="ongoing" @selected(($filters['status'] ?? '') === 'ongoing')>Ongoing</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="finance-btn finance-btn-primary text-xs px-4 py-2 flex-1">Apply Filters</button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('finance.reports.projects') }}" class="finance-btn finance-btn-secondary text-xs px-3 py-2">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Main Report Data Table / Cards -->
        @if($projects->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center shadow-sm">
                <div class="text-3xl mb-2">📊</div>
                <div class="text-base font-bold text-slate-800">
                    {{ !empty(array_filter($filters)) ? 'Nothing matches your filters.' : 'No projects found.' }}
                </div>
                @if(!empty(array_filter($filters)))
                    <div class="mt-3">
                        <a href="{{ route('finance.reports.projects') }}" class="text-xs font-semibold text-sky-700 hover:text-sky-900 underline">Clear Filters</a>
                    </div>
                @endif
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="hidden md:block bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3 text-right">Project Value</th>
                            <th class="px-4 py-3 text-right">Received</th>
                            <th class="px-4 py-3 text-right">Outstanding</th>
                            <th class="px-4 py-3 text-right">Approved Costs</th>
                            <th class="px-4 py-3 text-right">Est. Profit</th>
                            <th class="px-4 py-3 text-center no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        @foreach($projects as $project)
                            @php
                                $contractValue = (float) ($project->financial?->contract_value ?? 0);
                                $received = (float) $project->payments->sum('amount');
                                $outstanding = max(0, $contractValue - $received);
                                $approvedExpenses = (float) $project->financialExpenses->where('status', \App\Models\FinancialExpense::STATUS_APPROVED)->sum('amount');
                                $approvedMaterials = (float) $project->financialMaterialCosts->where('status', \App\Models\FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
                                $approvedCosts = $approvedExpenses + $approvedMaterials;
                                $profit = $contractValue - $approvedCosts;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $project->title ?: $project->project_code }}
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $project->project_code }} &bull; {{ ucfirst(str_replace('_', ' ', $project->status)) }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $project->client?->company_name ?: $project->client?->client_name ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold text-slate-900">
                                    {{ $financeMoney($contractValue) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium text-emerald-700">
                                    {{ $financeMoney($received) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium text-amber-700">
                                    {{ $financeMoney($outstanding) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium text-rose-700">
                                    {{ $financeMoney($approvedCosts) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-bold text-sky-700">
                                    {{ $financeMoney($profit) }}
                                </td>
                                <td class="px-4 py-3 text-center no-print">
                                    <a href="{{ route('finance.projects.show', $project) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-md transition-colors">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-3 mb-6">
                @foreach($projects as $project)
                    @php
                        $contractValue = (float) ($project->financial?->contract_value ?? 0);
                        $received = (float) $project->payments->sum('amount');
                        $outstanding = max(0, $contractValue - $received);
                        $approvedExpenses = (float) $project->financialExpenses->where('status', \App\Models\FinancialExpense::STATUS_APPROVED)->sum('amount');
                        $approvedMaterials = (float) $project->financialMaterialCosts->where('status', \App\Models\FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
                        $approvedCosts = $approvedExpenses + $approvedMaterials;
                        $profit = $contractValue - $approvedCosts;
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $project->title ?: $project->project_code }}</h3>
                                <div class="text-xs text-slate-500">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'N/A' }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs border-t border-slate-100 pt-2.5 mt-2">
                            <div>
                                <span class="text-slate-400">Value:</span>
                                <span class="font-semibold text-slate-800 block">{{ $financeMoney($contractValue) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Received:</span>
                                <span class="font-semibold text-emerald-700 block">{{ $financeMoney($received) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Outstanding:</span>
                                <span class="font-semibold text-amber-700 block">{{ $financeMoney($outstanding) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Est. Profit:</span>
                                <span class="font-semibold text-sky-700 block">{{ $financeMoney($profit) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 text-right border-t border-slate-100 pt-2 no-print">
                            <a href="{{ route('finance.projects.show', $project) }}" class="inline-block px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 rounded-lg">View Details &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="no-print">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
