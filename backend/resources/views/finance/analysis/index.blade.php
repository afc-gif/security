@extends('admin.layout')

@section('title', 'Finance Analysis | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        {{-- ── PAGE HEADER ──────────────────────────────────────────────────────── --}}
        <div class="finance-header mb-5">
            <div>
                <div class="text-xs font-bold tracking-wider text-sky-600 uppercase mb-1">Finance Intelligence</div>
                <h1 class="finance-title">Financial Analysis</h1>
                <p class="finance-subtitle">Money In, Money Out &amp; Net Position &mdash; {{ $periodLabel }}</p>
        {{-- ── PERIOD FILTER BAR ────────────────────────────────────────────────── --}}
        <form id="period-form" method="GET" action="{{ route('finance.analysis') }}" class="mb-6">
            <div class="flex flex-wrap items-center gap-2">
                @foreach([
                    'today'   => 'Today',
                    'week'    => 'This Week',
                    'month'   => 'This Month',
                    'quarter' => 'This Quarter',
                    'year'    => 'This Year',
                ] as $key => $label)
                    <a href="{{ route('finance.analysis', ['period' => $key, 'metric' => $metric]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors
                              {{ $period === $key ? 'bg-sky-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-sky-400 hover:text-sky-700' }}">
                        {{ $label }}
                    </a>
                @endforeach

                {{-- Custom range --}}
                <div class="flex items-center gap-1.5 ml-auto">
                    <input type="hidden" name="period" value="custom">
                    <input type="hidden" name="metric" value="{{ $metric }}">
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-sky-400 focus:outline-none"
                           placeholder="From">
                    <span class="text-xs text-slate-400">&ndash;</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-sky-400 focus:outline-none"
                           placeholder="To">
                    <button type="submit"
                            class="px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-sky-400 hover:text-sky-700 text-xs font-semibold text-slate-600 transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        </form>

        {{-- ── MONEY IN / OUT / NET (period) ───────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div id="card-total-in" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest text-emerald-600 uppercase">Money IN</div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m-8-8h16"/>
                        </svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-xl xl:text-3xl font-bold text-emerald-800 tabular-nums mb-3 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totalIn) }}">{{ $financeMoney($totalIn) }}</div>
                <div class="space-y-1 pt-2 border-t border-emerald-200 whitespace-nowrap overflow-hidden text-ellipsis">
                    {{-- Project Payments row with sub-breakdown --}}
                    <div class="flex justify-between text-xs">
                        <a href="{{ route('finance.projects.index') }}" class="text-emerald-700 hover:text-emerald-900 hover:underline font-medium">Project Payments &rarr;</a>
                        <span class="font-semibold text-emerald-800">{{ $financeMoney($projectRevenuePeriod) }}</span>
                    </div>
                    @if($projectRevenuePeriod > 0)
                        @php
                            $depositAmt     = $projectRevenueByType['deposit'] ?? 0;
                            $partAmt        = $projectRevenueByType['part_payment'] ?? 0;
                            $fullAmt        = $projectRevenueByType['full_payment'] ?? 0;
                        @endphp
                        @if($depositAmt > 0)
                        <div class="flex justify-between text-[10px] pl-3 text-emerald-600">
                            <span>Deposits</span>
                            <span>{{ $financeMoney($depositAmt) }}</span>
                        </div>
                        @endif
                        @if($partAmt > 0)
                        <div class="flex justify-between text-[10px] pl-3 text-emerald-600">
                            <span>Part Payments</span>
                            <span>{{ $financeMoney($partAmt) }}</span>
                        </div>
                        @endif
                        @if($fullAmt > 0)
                        <div class="flex justify-between text-[10px] pl-3 text-emerald-600">
                            <span>Full Payments</span>
                            <span>{{ $financeMoney($fullAmt) }}</span>
                        </div>
                        @endif
                    @endif
                    {{-- POS Sales row with link --}}
                    <div class="flex justify-between text-xs pt-1">
                        <span class="text-emerald-700">
                            <a href="{{ route('finance.pos-sales.index', ['period' => $period] + ($period === 'custom' ? ['date_from' => request('date_from'), 'date_to' => request('date_to')] : [])) }}"
                               class="hover:text-emerald-900 hover:underline font-medium">POS Sales &rarr;</a>
                            @if($posOrderCountPeriod > 0)
                                <span class="text-emerald-500">({{ $posOrderCountPeriod }})</span>
                            @endif
                        </span>
                        <span class="font-semibold text-emerald-800">{{ $financeMoney($posRevenuePeriod) }}</span>
                    </div>
                </div>
            </div>

            <div id="card-total-out" class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest text-rose-600 uppercase">Money OUT</div>
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-xl xl:text-3xl font-bold text-rose-800 tabular-nums mb-3 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totalOut) }}">{{ $financeMoney($totalOut) }}</div>
                <div class="space-y-1 pt-2 border-t border-rose-200 whitespace-nowrap overflow-hidden text-ellipsis">
                    <div class="flex justify-between text-xs">
                        <a href="{{ route('finance.office-expenses.index') }}" class="text-rose-700 hover:text-rose-900 hover:underline font-medium">Office Expenses &rarr;</a>
                        <span class="font-semibold text-rose-800">{{ $financeMoney($officeExpensesTotal) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <a href="{{ route('finance.expenses.index') }}" class="text-rose-700 hover:text-rose-900 hover:underline font-medium">Operational Expenses &rarr;</a>
                        <span class="font-semibold text-rose-800">{{ $financeMoney($operationalExpensesTotal) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-rose-700">Materials</span>
                        <span class="font-semibold text-rose-800">{{ $financeMoney($materialsPeriod) }}</span>
                    </div>
                </div>
            </div>

            <div id="card-net" class="rounded-xl border p-5 shadow-sm min-w-0 overflow-hidden {{ $netCashFlow >= 0 ? 'border-sky-200 bg-sky-50' : 'border-amber-200 bg-amber-50' }}">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest uppercase {{ $netCashFlow >= 0 ? 'text-sky-600' : 'text-amber-600' }}">Net Position</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $netCashFlow >= 0 ? 'bg-sky-100' : 'bg-amber-100' }}">
                        <svg class="w-4 h-4 {{ $netCashFlow >= 0 ? 'text-sky-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-xl xl:text-3xl font-bold tabular-nums mb-3 whitespace-nowrap overflow-hidden text-ellipsis {{ $netCashFlow >= 0 ? 'text-sky-800' : 'text-amber-800' }}" title="{{ $netCashFlow >= 0 ? '' : '−' }}{{ $financeMoney(abs($netCashFlow)) }}">
                    {{ $netCashFlow >= 0 ? '' : '−' }}{{ $financeMoney(abs($netCashFlow)) }}
                </div>
                <div class="pt-2 border-t whitespace-nowrap overflow-hidden text-ellipsis {{ $netCashFlow >= 0 ? 'border-sky-200' : 'border-amber-200' }}">
                    <div class="text-xs {{ $netCashFlow >= 0 ? 'text-sky-700' : 'text-amber-700' }}">
                        {{ $periodLabel }}: IN − OUT
                    </div>
                    @php
                        $allTimeReceivedTotal = $receivedTotal + ($posRevenuePeriod > 0 ? 0 : 0); // shown below separately
                    @endphp
                    <div class="text-xs text-slate-500 mt-0.5">All-time project position: {{ $financeMoney($estimatedProfitTotal) }}</div>
                </div>
            </div>
        </div>



        {{-- ── HEALTH BADGE ─────────────────────────────────────────────────────── --}}
        <div class="mb-6 p-4 rounded-xl border flex items-start gap-3
            {{ $healthStatus === 'healthy' ? 'border-emerald-200 bg-emerald-50' : ($healthStatus === 'watch' ? 'border-amber-200 bg-amber-50' : 'border-rose-200 bg-rose-50') }}">
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm font-bold
                {{ $healthStatus === 'healthy' ? 'bg-emerald-200 text-emerald-800' : ($healthStatus === 'watch' ? 'bg-amber-200 text-amber-800' : 'bg-rose-200 text-rose-800') }}">
                {{ $healthStatus === 'healthy' ? '✓' : ($healthStatus === 'watch' ? '⚠' : '!') }}
            </div>
            <div>
                <div class="text-sm font-bold
                    {{ $healthStatus === 'healthy' ? 'text-emerald-800' : ($healthStatus === 'watch' ? 'text-amber-800' : 'text-rose-800') }}">
                    Business Health: {{ $healthLabel }}
                </div>
                <div class="text-xs mt-0.5
                    {{ $healthStatus === 'healthy' ? 'text-emerald-700' : ($healthStatus === 'watch' ? 'text-amber-700' : 'text-rose-700') }}">
                    {{ $healthSummary }}
                </div>
            </div>
        </div>

        {{-- ── INSIGHTS ─────────────────────────────────────────────────────────── --}}
        @if(!empty($insights))
            <div class="mb-6">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Key Insights</h2>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach($insights as $insight)
                        @php
                            $insightColors = [
                                'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                                'danger'  => 'border-rose-200 bg-rose-50 text-rose-800',
                                'info'    => 'border-sky-200 bg-sky-50 text-sky-800',
                            ];
                            $ic = $insightColors[$insight['type']] ?? 'border-slate-200 bg-slate-50 text-slate-800';
                        @endphp
                        <div class="flex items-start gap-2.5 p-3 rounded-lg border {{ $ic }} text-xs">
                            <span class="shrink-0 mt-0.5">
                                @if($insight['type'] === 'success') ✓
                                @elseif($insight['type'] === 'warning') ⚠
                                @elseif($insight['type'] === 'danger') ✕
                                @else ℹ
                                @endif
                            </span>
                            <span>{{ $insight['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── CHARTS ROW ───────────────────────────────────────────────────────── --}}
        <div class="grid lg:grid-cols-3 gap-5 mb-6">

            {{-- Trend Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Revenue vs Expenses &mdash; {{ $periodLabel }}</h2>
                </div>
                <div id="trend-chart" style="height:220px;" class="relative">
                    @if(count($trendSeries) === 0)
                        <div class="absolute inset-0 flex items-center justify-center text-sm text-slate-400">No data for this period</div>
                    @else
                        <canvas id="trendCanvas"></canvas>
                    @endif
                </div>
            </div>

            {{-- Cost Breakdown Donut --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4">Expense Breakdown &mdash; {{ $periodLabel }}</h2>
                @if(empty($costBreakdown))
                    <div class="flex items-center justify-center h-40 text-sm text-slate-400">No approved expenses</div>
                @else
                    <div class="space-y-2">
                        @foreach(array_slice($costBreakdown, 0, 8) as $item)
                            <div>
                                <div class="flex justify-between text-xs mb-0.5">
                                    <span class="text-slate-600 truncate" style="max-width:65%">
                                        {{ $item['category'] }}
                                        @if($item['type'] === 'office')
                                            <span class="text-violet-500 text-[9px] ml-0.5">office</span>
                                        @elseif($item['type'] === 'materials')
                                            <span class="text-amber-500 text-[9px] ml-0.5">materials</span>
                                        @else
                                            <span class="text-slate-400 text-[9px] ml-0.5">operational</span>
                                        @endif
                                    </span>
                                    <span class="font-semibold text-slate-800">{{ $financeMoney($item['amount']) }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $item['type'] === 'office' ? 'bg-violet-400' : ($item['type'] === 'materials' ? 'bg-amber-400' : 'bg-sky-400') }}"
                                         data-pct="{{ $item['percentage'] }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if(count($costBreakdown) > 8)
                            <div class="text-[11px] text-slate-400 text-center pt-1">+ {{ count($costBreakdown) - 8 }} more categories</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ── TOP PROJECTS ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Project Performance</h2>
                <div class="flex gap-1.5">
                    @foreach(['profit' => 'Profit', 'revenue' => 'Revenue', 'costs' => 'Costs', 'outstanding' => 'Outstanding'] as $m => $ml)
                        <a href="{{ route('finance.analysis', ['period' => $period, 'metric' => $m, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                           class="px-2.5 py-1 rounded text-[11px] font-medium transition-colors
                                  {{ $metric === $m ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $ml }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if(empty($topProjects))
                <div class="py-8 text-center text-sm text-slate-400">No project data available</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left py-2 pr-3 font-semibold text-slate-500">Project</th>
                                <th class="text-right py-2 px-3 font-semibold text-slate-500">Contract</th>
                                <th class="text-right py-2 px-3 font-semibold text-slate-500">Received</th>
                                <th class="text-right py-2 px-3 font-semibold text-slate-500">Costs</th>
                                <th class="text-right py-2 px-3 font-semibold text-slate-500">Est. Profit</th>
                                <th class="text-right py-2 pl-3 font-semibold text-slate-500">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($topProjects as $pm)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2.5 pr-3">
                                        <a href="{{ route('finance.projects.show', $pm['id']) }}" class="font-semibold text-slate-800 hover:text-sky-700 truncate max-w-[160px] block">
                                            {{ $pm['title'] }}
                                        </a>
                                        <div class="text-slate-400 truncate max-w-[160px]">{{ $pm['client_name'] }}</div>
                                    </td>
                                    <td class="text-right py-2.5 px-3 tabular-nums text-slate-700">{{ $financeMoney($pm['value']) }}</td>
                                    <td class="text-right py-2.5 px-3 tabular-nums text-emerald-700">{{ $financeMoney($pm['received']) }}</td>
                                    <td class="text-right py-2.5 px-3 tabular-nums text-rose-700">{{ $financeMoney($pm['costs']) }}</td>
                                    <td class="text-right py-2.5 px-3 tabular-nums font-semibold {{ $pm['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $financeMoney($pm['profit']) }}
                                    </td>
                                    <td class="text-right py-2.5 pl-3 font-semibold {{ $pm['margin'] >= 20 ? 'text-emerald-600' : ($pm['margin'] >= 10 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ number_format($pm['margin'], 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── ATTENTION ITEMS ──────────────────────────────────────────────────── --}}
        @if(!empty($attentionItems))
            <div class="bg-white rounded-xl border border-rose-200 p-5 shadow-sm mb-6">
                <h2 class="text-sm font-bold text-rose-800 mb-4">Projects Requiring Attention</h2>
                <div class="divide-y divide-rose-100">
                    @foreach($attentionItems as $item)
                        <div class="py-3 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-sm text-slate-800">{{ $item['title'] }}</div>
                                <div class="text-xs text-rose-700 mt-0.5">{{ $item['explanation'] }}</div>
                            </div>
                            <a href="{{ $item['link'] }}" class="shrink-0 px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors">
                                View
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── AI Q&A PANEL ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Ask Finance</h2>
            <p class="text-xs text-slate-500 mb-4">Ask about POS revenue, diesel spend, salary costs, outstanding balances, project profitability and more. Add "this week", "this month", or "this year" for period context.</p>
            <form id="ask-form" class="flex gap-2" data-url="{{ route('finance.analysis.ask') }}">
                @csrf
                <input id="ask-question" type="text" name="question"
                       placeholder="e.g. How much did we spend on diesel this month?"
                       class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-200">
                <button type="submit"
                        class="px-4 py-2.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold transition-colors shrink-0">
                    Ask
                </button>
            </form>
            <div id="ask-answer" class="mt-3 hidden">
                <div id="ask-answer-text" class="p-3 rounded-lg bg-sky-50 border border-sky-200 text-sm text-sky-900"></div>
            </div>
        </div>

    </div>
</div>


<script type="application/json" id="trend-data">@json($trendSeries)</script>

@push('scripts')
<script>
// ── Progress bars: apply data-pct as width via JS (avoids Blade-in-style parse errors)
document.querySelectorAll('[data-pct]').forEach(function(el) {
    el.style.width = el.getAttribute('data-pct') + '%';
});

// ── Trend Chart ──────────────────────────────────────────────────────────────
const trendData = JSON.parse(document.getElementById('trend-data').textContent || '[]');

if (trendData.length > 0 && document.getElementById('trendCanvas')) {
    const labels   = trendData.map(d => d.label);
    const received = trendData.map(d => d.received);
    const pos      = trendData.map(d => d.pos);
    const costs    = trendData.map(d => d.costs);

    // Simple canvas bar chart — no external lib required
    const canvas  = document.getElementById('trendCanvas');
    const parent  = canvas.closest('#trend-chart');
    canvas.width  = parent.offsetWidth || 600;
    canvas.height = 220;
    const ctx     = canvas.getContext('2d');

    const PAD    = { top: 20, right: 10, bottom: 40, left: 52 };
    const W      = canvas.width  - PAD.left - PAD.right;
    const H      = canvas.height - PAD.top  - PAD.bottom;
    const N      = labels.length;

    const allVals = [...received, ...pos, ...costs];
    const maxVal  = Math.max(...allVals, 1);

    const groupW  = W / N;
    const barW    = Math.max(4, Math.min(18, groupW / 4 - 2));
    const gap     = (groupW - barW * 3) / 2;

    // Grid lines
    ctx.strokeStyle = '#f1f5f9';
    ctx.lineWidth   = 1;
    for (let i = 0; i <= 4; i++) {
        const y = PAD.top + H - (i / 4) * H;
        ctx.beginPath();
        ctx.moveTo(PAD.left, y);
        ctx.lineTo(PAD.left + W, y);
        ctx.stroke();

        // Y labels
        ctx.fillStyle   = '#94a3b8';
        ctx.font        = '9px system-ui, sans-serif';
        ctx.textAlign   = 'right';
        const val = (maxVal * i / 4);
        const label = val >= 1000000 ? '₦' + (val / 1000000).toFixed(1) + 'M'
                    : val >= 1000    ? '₦' + (val / 1000).toFixed(0) + 'K'
                    :                  '₦' + val.toFixed(0);
        ctx.fillText(label, PAD.left - 4, y + 3);
    }

    // Bars
    const colors = {
        received: '#10b981', // emerald
        pos:      '#8b5cf6', // violet
        costs:    '#f43f5e', // rose
    };

    function drawRoundedRect(ctx, x, y, w, h, r) {
        if (typeof r === 'number') {
            r = [r, r, r, r];
        }
        const [tl, tr, br, bl] = r;
        ctx.beginPath();
        ctx.moveTo(x + tl, y);
        ctx.lineTo(x + w - tr, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + tr);
        ctx.lineTo(x + w, y + h - br);
        ctx.quadraticCurveTo(x + w, y + h, x + w - br, y + h);
        ctx.lineTo(x + bl, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - bl);
        ctx.lineTo(x, y + tl);
        ctx.quadraticCurveTo(x, y, x + tl, y);
        ctx.closePath();
    }

    for (let i = 0; i < N; i++) {
        const cx = PAD.left + i * groupW + groupW / 2;
        const x0 = cx - (barW * 1.5 + gap);

        const drawBar = (x, val, color) => {
            const bH = (val / maxVal) * H;
            const y  = PAD.top + H - bH;
            ctx.fillStyle = color;
            drawRoundedRect(ctx, x, y, barW, bH, [2, 2, 0, 0]);
            ctx.fill();
        };

        drawBar(x0,              received[i], colors.received);
        drawBar(x0 + barW + 1,   pos[i],      colors.pos);
        drawBar(x0 + barW * 2 + 2, costs[i],  colors.costs);

        // X label — show subset if too many
        if (N <= 14 || i % Math.ceil(N / 12) === 0) {
            ctx.fillStyle = '#94a3b8';
            ctx.font      = '9px system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i], cx, PAD.top + H + 14);
        }
    }

    // Legend
    const legends = [['Project Payments', colors.received], ['POS Revenue', colors.pos], ['Expenses', colors.costs]];
    let lx = PAD.left;
    legends.forEach(([lbl, col]) => {
        ctx.fillStyle = col;
        drawRoundedRect(ctx, lx, PAD.top + H + 26, 8, 8, 2);
        ctx.fill();
        ctx.fillStyle = '#64748b';
        ctx.font      = '9px system-ui, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(lbl, lx + 12, PAD.top + H + 34);
        lx += ctx.measureText(lbl).width + 26;
    });
}

// ── Ask Finance Q&A ─────────────────────────────────────────────────────────
document.getElementById('ask-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const question = document.getElementById('ask-question').value.trim();
    if (!question) return;

    const btn = this.querySelector('button[type=submit]');
    btn.disabled  = true;
    btn.textContent = '...';

    const answerBox  = document.getElementById('ask-answer');
    const answerText = document.getElementById('ask-answer-text');
    answerBox.classList.add('hidden');

    try {
        const askUrl  = document.getElementById('ask-form').dataset.url;
        const resp = await fetch(askUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') || {}).content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ question }),
        });
        const data = await resp.json();
        answerText.textContent = data.answer ?? 'No answer returned.';
        if (data.project_url) {
            const link = document.createElement('a');
            link.href      = data.project_url;
            link.textContent = ' → View Project';
            link.className = 'ml-1 text-sky-700 font-semibold underline';
            answerText.appendChild(link);
        }
        answerBox.classList.remove('hidden');
    } catch (err) {
        answerText.textContent = 'An error occurred. Please try again.';
        answerBox.classList.remove('hidden');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Ask';
    }
});
</script>
@endpush
@endsection
