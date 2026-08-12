@extends('admin.layout')

@section('title', 'Finance Analysis & Intelligence | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-5">
            <div>
                <div class="text-xs font-bold tracking-wider text-sky-600 uppercase mb-1">ARTSCI Intelligence</div>
                <h1 class="finance-title">Finance Analysis</h1>
                <p class="finance-subtitle">Executive financial health assessment, trend tracking, cost breakdown, and smart insights.</p>
            </div>
        </div>

        <!-- 1. Executive Financial Health Header -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Financial Health</span>
                    @if($healthStatus === 'healthy')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Healthy
                        </span>
                    @elseif($healthStatus === 'watch')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Watch
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            Attention Required
                        </span>
                    @endif
                </div>
                <div class="text-xs font-medium text-slate-400">
                    Calculated from active project & operational records
                </div>
            </div>
            <p class="text-sm md:text-base font-semibold text-slate-800 leading-relaxed">
                {{ $healthSummary }}
            </p>
        </div>

        <!-- 2. Financial Trend Chart -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Financial Trend</h2>
                    <p class="text-xs text-slate-500">Project Value, Payments Received, and Approved Costs over time</p>
                </div>
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-semibold">
                    <a href="{{ route('finance.analysis', ['period' => '30D', 'metric' => $metric]) }}" class="px-2.5 py-1 rounded-md transition-colors {{ $period === '30D' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">30D</a>
                    <a href="{{ route('finance.analysis', ['period' => '3M', 'metric' => $metric]) }}" class="px-2.5 py-1 rounded-md transition-colors {{ $period === '3M' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">3M</a>
                    <a href="{{ route('finance.analysis', ['period' => '6M', 'metric' => $metric]) }}" class="px-2.5 py-1 rounded-md transition-colors {{ $period === '6M' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">6M</a>
                    <a href="{{ route('finance.analysis', ['period' => '1Y', 'metric' => $metric]) }}" class="px-2.5 py-1 rounded-md transition-colors {{ $period === '1Y' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">1Y</a>
                </div>
            </div>
            <div class="relative w-full h-64 md:h-72">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- 3. Cost Breakdown Donut & Project Performance Bar -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Donut: Cost Breakdown -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Cost Breakdown</h2>
                    <p class="text-xs text-slate-500 mb-4">Approved project expenses grouped by category & materials</p>
                    <div class="relative w-full h-56 flex items-center justify-center">
                        <canvas id="costBreakdownChart"></canvas>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                    @foreach($costBreakdown as $cb)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">{{ $cb['category'] }} ({{ $cb['percentage'] }}%)</span>
                            <span class="font-bold text-slate-900 tabular-nums">{{ $financeMoney($cb['amount']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Horizontal Bar: Project Performance -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Project Performance</h2>
                            <p class="text-xs text-slate-500">Top 5 projects by selected financial metric</p>
                        </div>
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-semibold">
                            <a href="{{ route('finance.analysis', ['period' => $period, 'metric' => 'profit']) }}" class="px-2 py-0.5 rounded transition-colors {{ $metric === 'profit' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600' }}">Profit</a>
                            <a href="{{ route('finance.analysis', ['period' => $period, 'metric' => 'revenue']) }}" class="px-2 py-0.5 rounded transition-colors {{ $metric === 'revenue' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600' }}">Revenue</a>
                            <a href="{{ route('finance.analysis', ['period' => $period, 'metric' => 'costs']) }}" class="px-2 py-0.5 rounded transition-colors {{ $metric === 'costs' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600' }}">Costs</a>
                            <a href="{{ route('finance.analysis', ['period' => $period, 'metric' => 'outstanding']) }}" class="px-2 py-0.5 rounded transition-colors {{ $metric === 'outstanding' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600' }}">Unpaid</a>
                        </div>
                    </div>
                    <div class="relative w-full h-64">
                        <canvas id="projectPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Smart Insights & Needs Attention -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Smart Insights -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg">💡</span>
                    <h2 class="text-base font-bold text-slate-900">Smart Insights</h2>
                </div>
                <div class="space-y-3">
                    @foreach($insights as $insight)
                        <div class="flex items-start gap-3 p-3 rounded-lg border text-xs leading-relaxed {{ $insight['type'] === 'danger' ? 'bg-rose-50 border-rose-100 text-rose-900' : ($insight['type'] === 'warning' ? 'bg-amber-50 border-amber-100 text-amber-900' : ($insight['type'] === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-800')) }}">
                            <span class="mt-0.5 text-sm">&bull;</span>
                            <div>{{ $insight['text'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Needs Attention -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg">⚠️</span>
                    <h2 class="text-base font-bold text-slate-900">Needs Attention</h2>
                </div>
                @if(empty($attentionItems))
                    <div class="p-6 text-center text-xs text-slate-500 bg-slate-50 rounded-lg border border-slate-100">
                        No critical financial warnings detected. All projects are operating within expected boundaries.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($attentionItems as $item)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-lg border border-amber-200 bg-amber-50/50">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $item['title'] }}</h4>
                                    <p class="text-[11px] text-slate-600 mt-0.5">{{ $item['explanation'] }}</p>
                                </div>
                                <a href="{{ $item['link'] }}" class="px-3 py-1 text-xs font-semibold text-sky-700 bg-white border border-sky-200 hover:bg-sky-50 rounded-md whitespace-nowrap shadow-sm">
                                    View Project
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 5. Ask Finance Assistant -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xl">🤖</span>
                <h2 class="text-base font-bold text-slate-900">Ask Finance</h2>
            </div>
            <p class="text-xs text-slate-500 mb-4">Ask about your projects, payments, costs or profitability.</p>

            <!-- Suggested Question Pills -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" onclick="askQuestion('Which project is most profitable?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    Which project is most profitable?
                </button>
                <button type="button" onclick="askQuestion('Which clients have the largest outstanding balances?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    Which clients have largest outstanding balances?
                </button>
                <button type="button" onclick="askQuestion('How much have we spent on transportation?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    How much spent on transportation?
                </button>
                <button type="button" onclick="askQuestion('Which projects are over budget?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    Which projects are over budget?
                </button>
                <button type="button" onclick="askQuestion('How much have we received this month?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    How much received this month?
                </button>
                <button type="button" onclick="askQuestion('What is our estimated profit?')" class="px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                    What is our estimated profit?
                </button>
            </div>

            <!-- Chat History -->
            <div id="chatHistory" class="space-y-3 mb-4 max-h-80 overflow-y-auto p-3 bg-slate-50 rounded-lg border border-slate-100 text-xs">
                <div class="flex items-start gap-2.5">
                    <span class="text-base">🤖</span>
                    <div class="bg-white p-3 rounded-lg border border-slate-200 text-slate-800 shadow-sm leading-relaxed max-w-2xl">
                        Hello! I am your ARTSCI Finance Assistant. Select a suggested question above or type your question below.
                    </div>
                </div>
            </div>

            <!-- Chat Input Form -->
            <form id="askForm" onsubmit="handleAskSubmit(event)" class="flex items-center gap-2">
                @csrf
                <input id="askInput" type="text" placeholder="Type a financial question..." class="flex-1 text-xs rounded-lg border border-slate-300 px-3.5 py-2.5 bg-white shadow-sm focus:border-sky-500 focus:outline-none">
                <button type="submit" class="finance-btn finance-btn-primary px-4 py-2.5 text-xs">
                    Send &rarr;
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') return;

        // 1. Trend Line Chart
        const trendData = JSON.parse('{!! addslashes(json_encode($trendSeries)) !!}');
        const trendCtx = document.getElementById('trendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Project Value',
                            data: trendData.map(d => d.value),
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2, 132, 199, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Received',
                            data: trendData.map(d => d.received),
                            borderColor: '#059669',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.3,
                        },
                        {
                            label: 'Approved Costs',
                            data: trendData.map(d => d.costs),
                            borderColor: '#e11d48',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += '₦' + new Intl.NumberFormat().format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return '₦' + (value/1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return '₦' + (value/1000).toFixed(0) + 'K';
                                    return '₦' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Cost Breakdown Donut Chart
        const breakdownData = JSON.parse('{!! addslashes(json_encode($costBreakdown)) !!}');
        const donutCtx = document.getElementById('costBreakdownChart')?.getContext('2d');
        if (donutCtx && breakdownData.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: breakdownData.map(b => b.category),
                    datasets: [{
                        data: breakdownData.map(b => b.amount),
                        backgroundColor: ['#0284c7', '#059669', '#d97706', '#e11d48', '#8b5cf6', '#64748b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const amount = context.parsed;
                                    return context.label + ': ₦' + new Intl.NumberFormat().format(amount);
                                }
                            }
                        }
                    },
                    cutout: '68%',
                }
            });
        }

        // 3. Project Performance Horizontal Bar Chart
        const topProjectsData = JSON.parse('{!! addslashes(json_encode($topProjects)) !!}');
        const metric = JSON.parse('{!! addslashes(json_encode($metric)) !!}');
        const barCtx = document.getElementById('projectPerformanceChart')?.getContext('2d');
        if (barCtx && topProjectsData.length > 0) {
            const metricKey = metric === 'revenue' ? 'value' : (metric === 'costs' ? 'costs' : (metric === 'outstanding' ? 'outstanding' : 'profit'));
            const barLabel = metric === 'revenue' ? 'Contract Value' : (metric === 'costs' ? 'Approved Costs' : (metric === 'outstanding' ? 'Outstanding Balance' : 'Estimated Profit'));
            const barColor = metric === 'costs' ? '#e11d48' : (metric === 'outstanding' ? '#d97706' : '#0284c7');

            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: topProjectsData.map(p => p.title.length > 18 ? p.title.substring(0, 18) + '...' : p.title),
                    datasets: [{
                        label: barLabel,
                        data: topProjectsData.map(p => p[metricKey]),
                        backgroundColor: barColor,
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return barLabel + ': ₦' + new Intl.NumberFormat().format(context.parsed.x);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return '₦' + (value/1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return '₦' + (value/1000).toFixed(0) + 'K';
                                    return '₦' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // Ask Finance Interactive Handlers
    function askQuestion(text) {
        document.getElementById('askInput').value = text;
        handleAskSubmit(new Event('submit'));
    }

    function handleAskSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('askInput');
        const question = input.value.trim();
        if (!question) return;

        const history = document.getElementById('chatHistory');

        // Append user question
        const userDiv = document.createElement('div');
        userDiv.className = 'flex items-start justify-end gap-2.5';
        userDiv.innerHTML = `<div class="bg-sky-600 text-white p-3 rounded-lg text-xs leading-relaxed max-w-2xl shadow-sm">${escapeHtml(question)}</div><span class="text-base">👤</span>`;
        history.appendChild(userDiv);

        input.value = '';
        history.scrollTop = history.scrollHeight;

        // Append typing indicator
        const typingDiv = document.createElement('div');
        typingDiv.className = 'flex items-start gap-2.5';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `<span class="text-base">🤖</span><div class="bg-white p-3 rounded-lg border border-slate-200 text-slate-400 text-xs shadow-sm">Thinking...</div>`;
        history.appendChild(typingDiv);
        history.scrollTop = history.scrollHeight;

        fetch("{{ route('finance.analysis.ask') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ question: question })
        })
        .then(res => res.json())
        .then(data => {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();

            const botDiv = document.createElement('div');
            botDiv.className = 'flex items-start gap-2.5';

            let actionBtn = '';
            if (data.project_url) {
                actionBtn = `<div class="mt-2.5"><a href="${data.project_url}" class="inline-block px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-200 rounded-md hover:bg-sky-100">View Project &rarr;</a></div>`;
            }

            botDiv.innerHTML = `<span class="text-base">🤖</span><div class="bg-white p-3 rounded-lg border border-slate-200 text-slate-800 shadow-sm leading-relaxed max-w-2xl">${escapeHtml(data.answer)}${actionBtn}</div>`;
            history.appendChild(botDiv);
            history.scrollTop = history.scrollHeight;
        })
        .catch(err => {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();

            const errDiv = document.createElement('div');
            errDiv.className = 'flex items-start gap-2.5';
            errDiv.innerHTML = `<span class="text-base">🤖</span><div class="bg-rose-50 p-3 rounded-lg border border-rose-200 text-rose-800 text-xs">Sorry, I encountered an issue retrieving the response. Please try again.</div>`;
            history.appendChild(errDiv);
            history.scrollTop = history.scrollHeight;
        });
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endsection
