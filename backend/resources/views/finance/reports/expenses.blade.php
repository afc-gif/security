@extends('admin.layout')

@section('title', 'Expense Report | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-4">
            <div>
                <a href="{{ route('finance.reports.index') }}" class="text-xs font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1 mb-2 no-print">
                    &larr; Back to Reports
                </a>
                <h1 class="finance-title">Expense Report</h1>
                <p class="finance-subtitle">Comprehensive tracking of approved and pending expenses across projects and jobs.</p>
            </div>
            <div class="flex items-center gap-2 mt-3 sm:mt-0 no-print">
                <button type="button" onclick="window.print()" class="finance-btn finance-btn-secondary px-3 py-1.5 text-xs">
                    🖨️ Print
                </button>
                <a href="{{ route('finance.reports.expenses.export', request()->query()) }}" class="finance-btn finance-btn-primary px-3 py-1.5 text-xs">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Filter-Aware Totals Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Expenses</div>
                <div class="text-lg md:text-xl font-bold text-slate-900 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totals['total']) }}">{{ $financeMoney($totals['total']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Approved</div>
                <div class="text-lg md:text-xl font-bold text-emerald-700 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totals['approved']) }}">{{ $financeMoney($totals['approved']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-amber-600 uppercase tracking-wider">Pending Review</div>
                <div class="text-lg md:text-xl font-bold text-amber-700 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totals['pending']) }}">{{ $financeMoney($totals['pending']) }}</div>
            </div>
        </div>



        <!-- Filter Bar -->
        <form method="GET" action="{{ route('finance.reports.expenses') }}" class="bg-white rounded-xl border border-slate-200 p-4 mb-6 shadow-sm no-print">
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Date From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Date To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="category" class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select id="category" name="category" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Statuses</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Search</label>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Description, project..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-end gap-2">
                @if(!empty(array_filter($filters)))
                    <a href="{{ route('finance.reports.expenses') }}" class="finance-btn finance-btn-secondary text-xs px-3 py-2">Clear</a>
                @endif
                <button type="submit" class="finance-btn finance-btn-primary text-xs px-4 py-2">Apply Filters</button>
            </div>
        </form>

        <!-- Main Report Data Table / Cards -->
        @if($expenses->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center shadow-sm">
                <div class="text-3xl mb-2">💸</div>
                <div class="text-base font-bold text-slate-800">
                    {{ !empty(array_filter($filters)) ? 'Nothing matches your filters.' : 'No expenses found.' }}
                </div>
                @if(!empty(array_filter($filters)))
                    <div class="mt-3">
                        <a href="{{ route('finance.reports.expenses') }}" class="text-xs font-semibold text-sky-700 hover:text-sky-900 underline">Clear Filters</a>
                    </div>
                @endif
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="hidden md:block bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Project / Job</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        @foreach($expenses as $expense)
                            @php
                                $entity = $expense->project?->title
                                    ?: ($expense->jobRequestItem?->title ?: ($expense->inspection?->title ?: 'General Expense'));

                                $client = $expense->project?->client?->company_name
                                    ?: ($expense->project?->client?->client_name
                                    ?: ($expense->jobRequestItem?->jobRequest?->client?->company_name
                                    ?: ($expense->jobRequestItem?->jobRequest?->client?->client_name ?: 'N/A')));
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                                    {{ $expense->incurred_on ? $expense->incurred_on->format('d M Y') : $expense->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $entity }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $client }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800">
                                        {{ $expense->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                                    {{ $expense->description }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-bold text-slate-900">
                                    {{ $financeMoney($expense->amount) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($expense->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">Approved</span>
                                    @elseif($expense->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">Pending</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-3 mb-6">
                @foreach($expenses as $expense)
                    @php
                        $entity = $expense->project?->title
                            ?: ($expense->jobRequestItem?->title ?: ($expense->inspection?->title ?: 'General Expense'));

                        $client = $expense->project?->client?->company_name
                            ?: ($expense->project?->client?->client_name
                            ?: ($expense->jobRequestItem?->jobRequest?->client?->company_name
                            ?: ($expense->jobRequestItem?->jobRequest?->client?->client_name ?: 'N/A')));
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $entity }}</h3>
                                <div class="text-xs text-slate-500">{{ $client }} &bull; {{ $expense->category?->name ?? 'Uncategorized' }}</div>
                            </div>
                            @if($expense->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Approved</span>
                            @elseif($expense->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">Pending</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700">Rejected</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-700 mb-2">{{ $expense->description }}</div>
                        <div class="flex justify-between items-center text-xs border-t border-slate-100 pt-2.5 mt-2">
                            <span class="text-slate-400">{{ $expense->incurred_on ? $expense->incurred_on->format('d M Y') : $expense->created_at->format('d M Y') }}</span>
                            <span class="font-bold text-slate-900 text-sm tabular-nums">{{ $financeMoney($expense->amount) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="no-print">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
