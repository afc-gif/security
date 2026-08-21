@extends('admin.layout')

@section('title', 'Payment Report | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-4">
            <div>
                <a href="{{ route('finance.reports.index') }}" class="text-xs font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1 mb-2 no-print">
                    &larr; Back to Reports
                </a>
                <h1 class="finance-title">Payment Report</h1>
                <p class="finance-subtitle">Comprehensive ledger of client payments received across all projects.</p>
            </div>
            <div class="flex items-center gap-2 mt-3 sm:mt-0 no-print">
                <button type="button" onclick="window.print()" class="finance-btn finance-btn-secondary px-3 py-1.5 text-xs">
                    🖨️ Print
                </button>
                <a href="{{ route('finance.reports.payments.export', request()->query()) }}" class="finance-btn finance-btn-primary px-3 py-1.5 text-xs">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Filter-Aware Totals Summary -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Total Received</div>
                <div class="text-lg md:text-xl font-bold text-emerald-700 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totals['total_received']) }}">{{ $financeMoney($totals['total_received']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Number of Payments</div>
                <div class="text-lg md:text-xl font-bold text-slate-900 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ number_format($totals['payment_count']) }}">{{ number_format($totals['payment_count']) }}</div>
            </div>
        </div>



        <!-- Filter Bar -->
        <form method="GET" action="{{ route('finance.reports.payments') }}" class="bg-white rounded-xl border border-slate-200 p-4 mb-6 shadow-sm no-print">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Date From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Date To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="payment_method" class="block text-xs font-semibold text-slate-700 mb-1">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Methods</option>
                        <option value="bank_transfer" @selected(($filters['payment_method'] ?? '') === 'bank_transfer')>Bank Transfer</option>
                        <option value="cash" @selected(($filters['payment_method'] ?? '') === 'cash')>Cash</option>
                        <option value="pos" @selected(($filters['payment_method'] ?? '') === 'pos')>POS</option>
                        <option value="check" @selected(in_array(($filters['payment_method'] ?? ''), ['check', 'cheque']))>Cheque</option>
                        <option value="other" @selected(($filters['payment_method'] ?? '') === 'other')>Other</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Search</label>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Project, client, or reference..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-end gap-2">
                @if(!empty(array_filter($filters)))
                    <a href="{{ route('finance.reports.payments') }}" class="finance-btn finance-btn-secondary text-xs px-3 py-2">Clear</a>
                @endif
                <button type="submit" class="finance-btn finance-btn-primary text-xs px-4 py-2">Apply Filters</button>
            </div>
        </form>

        <!-- Main Report Data Table / Cards -->
        @if($payments->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center shadow-sm">
                <div class="text-3xl mb-2">💳</div>
                <div class="text-base font-bold text-slate-800">
                    {{ !empty(array_filter($filters)) ? 'Nothing matches your filters.' : 'No payments found.' }}
                </div>
                @if(!empty(array_filter($filters)))
                    <div class="mt-3">
                        <a href="{{ route('finance.reports.payments') }}" class="text-xs font-semibold text-sky-700 hover:text-sky-900 underline">Clear Filters</a>
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
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                                    {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $payment->project?->title ?: $payment->project?->project_code }}
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $payment->project?->project_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $payment->project?->client?->company_name ?: $payment->project?->client?->client_name ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-mono text-[11px]">
                                    {{ $payment->reference ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-bold text-emerald-700">
                                    {{ $financeMoney($payment->amount) }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-500 text-[11px]">
                                    {{ $payment->recorder?->name ?? 'System' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-3 mb-6">
                @foreach($payments as $payment)
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $payment->project?->title ?: $payment->project?->project_code }}</h3>
                                <div class="text-xs text-slate-500">{{ $payment->project?->client?->company_name ?: $payment->project?->client?->client_name ?: 'N/A' }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </div>
                        @if($payment->reference)
                            <div class="text-xs text-slate-600 font-mono mb-2">Ref: {{ $payment->reference }}</div>
                        @endif
                        <div class="flex justify-between items-center text-xs border-t border-slate-100 pt-2.5 mt-2">
                            <span class="text-slate-400">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '' }}</span>
                            <span class="font-bold text-emerald-700 text-sm tabular-nums">{{ $financeMoney($payment->amount) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="no-print">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
