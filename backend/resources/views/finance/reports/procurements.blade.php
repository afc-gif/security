@extends('admin.layout')

@section('title', 'Procurement Report | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <div class="text-xs font-bold tracking-wider text-indigo-600 uppercase mb-1">ARTSCI Finance</div>
                <h1 class="finance-title">Procurement Report</h1>
                <p class="finance-subtitle">Comprehensive ledger of inventory purchases and supplier spending.</p>
            </div>
            <div class="flex items-center gap-2 mt-3 sm:mt-0 no-print">
                <button type="button" onclick="window.print()" class="finance-btn finance-btn-secondary px-3 py-1.5 text-xs">
                    🖨️ Print
                </button>
                <a href="{{ route('finance.reports.procurements.export', request()->query()) }}" class="finance-btn finance-btn-primary px-3 py-1.5 text-xs bg-indigo-600 text-white hover:bg-indigo-700">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Filter-Aware Totals Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Purchases Cost</div>
                <div class="text-lg md:text-xl font-bold text-slate-900 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totals['total_spend']) }}">{{ $financeMoney($totals['total_spend']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-indigo-600 uppercase tracking-wider">Total Quantity Bought</div>
                <div class="text-lg md:text-xl font-bold text-indigo-700 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ number_format($totals['total_quantity'], 2) }}">{{ number_format($totals['total_quantity'], 2) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Number of Orders</div>
                <div class="text-lg md:text-xl font-bold text-slate-900 tabular-nums mt-0.5 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ number_format($totals['purchase_count']) }}">{{ number_format($totals['purchase_count']) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('finance.reports.procurements') }}" class="bg-white rounded-xl border border-slate-200 p-4 mb-6 shadow-sm no-print">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label for="supplier_id" class="block text-xs font-semibold text-slate-700 mb-1">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="inventory_product_id" class="block text-xs font-semibold text-slate-700 mb-1">Product</label>
                    <select id="inventory_product_id" name="inventory_product_id" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">All Products</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" @selected(request('inventory_product_id') == $prod->id)>{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Date From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Date To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('finance.reports.procurements') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Reset</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm">Filter Report</button>
            </div>
        </form>

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Purchase Records</h2>
                <span class="text-xs text-slate-400">Total matched: {{ $procurements->count() }}</span>
            </div>

            @if($procurements->isEmpty())
                <div class="px-5 py-12 text-center text-slate-400 text-sm">
                    No purchases found matching the active filters.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-5 py-2.5 text-left font-semibold">Date</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Supplier</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Product</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Quantity</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Unit Cost</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Total Cost</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($procurements as $proc)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $proc->purchase_date->format('M d, Y') }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-800 whitespace-nowrap">{{ $proc->supplier->name }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $proc->product->name }}</td>
                                    <td class="px-5 py-3 text-right text-slate-800 tabular-nums whitespace-nowrap">{{ number_format($proc->quantity, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-slate-800 tabular-nums whitespace-nowrap">{{ $financeMoney($proc->unit_cost) }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-slate-900 tabular-nums whitespace-nowrap">{{ $financeMoney($proc->total_cost) }}</td>
                                    <td class="px-5 py-3 text-slate-500 max-w-xs truncate" title="{{ $proc->notes }}">{{ $proc->notes ?? '-' }}</td>
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
