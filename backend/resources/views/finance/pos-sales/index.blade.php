@extends('admin.layout')

@section('title', 'POS Sales | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-6">
            <div>
                <div class="text-xs font-bold tracking-wider text-violet-600 uppercase mb-1">ARTSCI Finance</div>
                <h1 class="finance-title">POS Sales</h1>
                <p class="finance-subtitle">Completed POS revenue &mdash; read-only view</p>
            </div>
            <div class="mt-3 sm:mt-0 flex gap-2">
                <a href="{{ route('finance.analysis') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition-colors">
                    Full Analysis
                </a>
            </div>
        </div>

        {{-- ── PERIOD FILTER BAR ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm mb-5">
            <form method="GET" action="{{ route('finance.pos-sales.index') }}" class="flex flex-wrap items-center gap-2">
                @foreach ([
                    'today'   => 'Today',
                    'week'    => 'This Week',
                    'month'   => 'This Month',
                    'quarter' => 'This Quarter',
                    'year'    => 'This Year',
                ] as $key => $label)
                    <a href="{{ route('finance.pos-sales.index', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $period === $key ? 'bg-violet-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach

                {{-- Custom range --}}
                <span class="h-4 w-px bg-slate-200 mx-1 hidden sm:block"></span>
                <input type="date" name="date_from"
                       value="{{ request('date_from', $period === 'custom' ? $from->toDateString() : '') }}"
                       class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400">
                <input type="date" name="date_to"
                       value="{{ request('date_to', $period === 'custom' ? $to->toDateString() : '') }}"
                       class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400">
                <button type="submit" name="period" value="custom"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $period === 'custom' ? 'bg-violet-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    Apply
                </button>
            </form>
        </div>

        {{-- ── SUMMARY CARDS ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm col-span-2 md:col-span-2 min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-violet-600 uppercase tracking-wider mb-1">
                    POS Revenue &mdash; {{ $periodLabel }}
                </div>
                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-violet-700 tabular-nums break-all">
                    {{ $financeMoney($periodTotal) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 break-all">
                    {{ $periodCount }} completed sale{{ $periodCount !== 1 ? 's' : '' }}
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm col-span-2 md:col-span-2 min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">All-Time POS Revenue</div>
                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-700 tabular-nums break-all">
                    {{ $financeMoney($allTimeTotal) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 break-all">
                    {{ $allTimeCount }} total completed sale{{ $allTimeCount !== 1 ? 's' : '' }}
                </div>
            </div>


        </div>

        {{-- ── SALES TABLE ────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">
                    Completed Sales &mdash; <span class="font-normal text-violet-600">{{ $periodLabel }}</span>
                </h2>
                <span class="text-xs text-slate-400">Showing {{ $orders->count() }} of {{ $orders->total() }}</span>
            </div>

            @if($orders->isEmpty())
                <div class="px-5 py-12 text-center text-slate-400 text-sm">
                    No completed POS sales found for <strong>{{ $periodLabel }}</strong>.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-5 py-2.5 text-left font-semibold">Date</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Order</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Items</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Amount</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 text-slate-500 text-[12px] whitespace-nowrap">
                                        {{ $order->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 text-[12px]">
                                        {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-900 tabular-nums">
                                        {{ $financeMoney($order->total_amount) }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 text-[12px] capitalize">
                                        {{ $order->payment_method ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-xs font-bold text-slate-700">
                                    Period Total ({{ $periodLabel }})
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-violet-700 tabular-nums">
                                    {{ $financeMoney($periodTotal) }}
                                </td>
                                <td class="px-5 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($orders->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- ── ACCOUNTING NOTE ────────────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-violet-100 bg-violet-50 px-5 py-4 text-xs text-violet-700 mb-4">
            <strong>Finance Note:</strong> Only <strong>completed</strong> POS orders are counted as Money In.
            Pending, cancelled, and failed orders are excluded. This view is read-only &mdash;
            use the <a href="{{ route('pos.index') }}" class="underline font-semibold">POS module</a> to manage sales.
        </div>

    </div>
</div>
@endsection
