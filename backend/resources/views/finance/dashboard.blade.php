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
                <p class="finance-subtitle">Financial Overview for Active & Project Operations</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <!-- Top Financial Snapshot (5 Metrics) -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-6">
            <!-- Metric 1: Project Value -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Project Value</div>
                <div class="text-xl md:text-2xl font-bold text-slate-900 tabular-nums">{{ $financeMoney($projectValueTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Total Contract Value</div>
            </div>

            <!-- Metric 2: Received -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-xs font-medium text-emerald-600 uppercase tracking-wider mb-1">Received</div>
                <div class="text-xl md:text-2xl font-bold text-emerald-700 tabular-nums">{{ $financeMoney($receivedTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Client Payments</div>
            </div>

            <!-- Metric 3: Outstanding -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-1">Outstanding</div>
                <div class="text-xl md:text-2xl font-bold text-amber-700 tabular-nums">{{ $financeMoney($outstandingTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Balance Due</div>
            </div>

            <!-- Metric 4: Approved Costs -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-xs font-medium text-rose-600 uppercase tracking-wider mb-1">Approved Costs</div>
                <div class="text-xl md:text-2xl font-bold text-rose-700 tabular-nums">{{ $financeMoney($approvedCostsTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Expenses + Materials</div>
            </div>

            <!-- Metric 5: Estimated Profit -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow col-span-2 lg:col-span-1">
                <div class="text-xs font-medium text-sky-600 uppercase tracking-wider mb-1">Estimated Profit</div>
                <div class="text-xl md:text-2xl font-bold text-sky-700 tabular-nums">{{ $financeMoney($estimatedProfitTotal) }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Value − Approved Costs</div>
            </div>
        </div>

        <!-- Attention / Action Area (Conditionally Rendered) -->
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

        <!-- Recent Jobs & Recent Projects Section -->
        <div class="grid lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Jobs -->
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

            <!-- Recent Projects -->
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
