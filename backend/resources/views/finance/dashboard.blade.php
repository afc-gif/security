@extends('admin.layout')

@section('title', 'Finance Overview | ARTSCI')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-6">
            <div>
                <div class="text-xs font-bold tracking-wider text-sky-600 uppercase mb-1">ARTSCI Finance</div>
                <h1 class="finance-title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()?->name ?? 'Finance User' }}</h1>
                <p class="finance-subtitle">Financial Overview &mdash; All Sources &middot; All Time</p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('finance.analysis') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Full Analysis
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- ── TOTAL IN / OUT / NET ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-4">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[10px] font-bold tracking-widest text-emerald-600 uppercase mb-1">Total Money IN</div>
                <div class="text-xl sm:text-2xl font-bold text-emerald-800 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totalIn) }}">{{ $financeMoney($totalIn) }}</div>
                <div class="text-[11px] text-emerald-600 mt-1.5 space-y-0.5 whitespace-nowrap overflow-hidden text-ellipsis">
                    <div class="truncate" title="Project payments: {{ $financeMoney($receivedTotal) }}">Project payments: {{ $financeMoney($receivedTotal) }}</div>
                    <div class="truncate" title="POS Sales: {{ $financeMoney($posRevenueTotal) }}">POS Sales: {{ $financeMoney($posRevenueTotal) }}</div>
                </div>
            </div>

            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm min-w-0 overflow-hidden">
                <div class="text-[10px] font-bold tracking-widest text-rose-600 uppercase mb-1">Total Money OUT</div>
                <div class="text-xl sm:text-2xl font-bold text-rose-800 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($totalOut) }}">{{ $financeMoney($totalOut) }}</div>
                <div class="text-[11px] text-rose-600 mt-1.5 whitespace-nowrap overflow-hidden text-ellipsis">
                    Approved expenses &amp; materials
                </div>
            </div>

            <div class="rounded-xl border p-4 shadow-sm min-w-0 overflow-hidden {{ $netCashPosition >= 0 ? 'border-sky-200 bg-sky-50' : 'border-amber-200 bg-amber-50' }}">
                <div class="text-[10px] font-bold tracking-widest uppercase mb-1 {{ $netCashPosition >= 0 ? 'text-sky-600' : 'text-amber-600' }}">Net Position</div>
                <div class="text-xl sm:text-2xl font-bold tabular-nums whitespace-nowrap overflow-hidden text-ellipsis {{ $netCashPosition >= 0 ? 'text-sky-800' : 'text-amber-800' }}" title="{{ $financeMoney($netCashPosition) }}">{{ $financeMoney($netCashPosition) }}</div>
                <div class="text-[11px] mt-1.5 whitespace-nowrap overflow-hidden text-ellipsis {{ $netCashPosition >= 0 ? 'text-sky-600' : 'text-amber-600' }}">
                    Total IN &minus; Total OUT (all-time)
                </div>
            </div>
        </div>

        {{-- ── DETAILED METRICS (5 columns) ────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Project Value</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold text-slate-900 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($projectValueTotal) }}">{{ $financeMoney($projectValueTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">Total Contract Value</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-emerald-600 uppercase tracking-wider mb-1">Project Received</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold text-emerald-700 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($receivedTotal) }}">{{ $financeMoney($receivedTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">Client Payments</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-violet-600 uppercase tracking-wider mb-1">POS Revenue</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold text-violet-700 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($posRevenueTotal) }}">{{ $financeMoney($posRevenueTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">
                    This month: {{ $financeMoney($posRevenueThisMonth) }}
                    @if($posOrderCountThisMonth > 0)
                        ({{ $posOrderCountThisMonth }} sale{{ $posOrderCountThisMonth !== 1 ? 's' : '' }})
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-1">Outstanding</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold text-amber-700 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($outstandingTotal) }}">{{ $financeMoney($outstandingTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">Project Balance Due</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow col-span-2 lg:col-span-1 min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-rose-600 uppercase tracking-wider mb-1">Approved Costs</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold text-rose-700 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($approvedCostsTotal) }}">{{ $financeMoney($approvedCostsTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">Expenses + Materials</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow col-span-2 lg:col-span-1 min-w-0 overflow-hidden">
                <div class="text-xs font-medium text-indigo-600 uppercase tracking-wider mb-1">Est. Project Profit</div>
                <div class="text-base sm:text-lg lg:text-sm xl:text-lg 2xl:text-xl font-bold {{ $estimatedProfitTotal >= 0 ? 'text-indigo-700' : 'text-rose-700' }} tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($estimatedProfitTotal) }}">{{ $financeMoney($estimatedProfitTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1 whitespace-nowrap overflow-hidden text-ellipsis">Contract Value &minus; Costs</div>
            </div>
        </div>



        {{-- ── ATTENTION ITEMS ───────────────────────────────────────────────────── --}}
        @if(!empty($attentionItems))
            <div class="mb-6">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Needs Attention</h2>
                <div class="grid gap-3">
                    @foreach($attentionItems as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-800 font-bold text-sm shrink-0">!</span>
                                <div>
                                    <div class="font-bold text-sm text-amber-950">{{ $item['title'] }}</div>
                                    <div class="text-xs text-amber-800">{{ $item['description'] }}</div>
                                </div>
                            </div>
                            <a href="{{ $item['link'] }}" class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-xs font-semibold shrink-0 transition-colors">
                                {{ $item['link_text'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── RECENT JOBS & PROJECTS ────────────────────────────────────────────── --}}
        <div class="grid lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Recent Jobs</h2>
                    <a href="{{ route('finance.jobs.index') }}" class="text-xs font-semibold text-sky-700 hover:text-sky-900">View All Jobs &rarr;</a>
                </div>

                @if($recentJobs->isEmpty())
                    <div class="py-8 text-center">
                        <div class="text-2xl mb-1">📋</div>
                        <div class="text-sm font-semibold text-slate-700">No jobs yet</div>
                        <div class="text-xs text-slate-500 mt-1">Jobs will appear here as field operations start</div>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentJobs as $job)
                            @php($client = $job->jobRequest?->client)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-slate-900 truncate">{{ $job->title ?: $job->jobRequest?->title ?: 'Untitled Job' }}</div>
                                    <div class="text-xs text-slate-500 truncate mt-0.5">{{ $client?->company_name ?: $client?->client_name ?: 'Client N/A' }}</div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}
                                    </span>
                                    <a href="{{ route('finance.jobs.show', $job) }}" class="px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Recent Projects</h2>
                    <a href="{{ route('finance.projects.index') }}" class="text-xs font-semibold text-sky-700 hover:text-sky-900">View All Projects &rarr;</a>
                </div>

                @if($recentProjects->isEmpty())
                    <div class="py-8 text-center">
                        <div class="text-2xl mb-1">🏗️</div>
                        <div class="text-sm font-semibold text-slate-700">No projects yet</div>
                        <div class="text-xs text-slate-500 mt-1">Projects will appear here once initialized</div>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentProjects as $project)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-slate-900 truncate">{{ $project->title ?: $project->project_code }}</div>
                                    <div class="text-xs text-slate-500 truncate mt-0.5">
                                        {{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client N/A' }}
                                        @if($project->financial?->contract_value !== null)
                                            &bull; <span class="font-medium text-slate-700">{{ $financeMoney($project->financial->contract_value) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}
                                    </span>
                                    <a href="{{ route('finance.projects.show', $project) }}" class="px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
