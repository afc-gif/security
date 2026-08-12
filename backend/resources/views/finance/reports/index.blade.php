@extends('admin.layout')

@section('title', 'Reports | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header mb-6">
            <div>
                <div class="text-xs font-bold tracking-wider text-sky-600 uppercase mb-1">ARTSCI Finance</div>
                <h1 class="finance-title">Reports</h1>
                <p class="finance-subtitle">Financial information and reports for ARTSCI projects and operations.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            <!-- Option 1: Project Financials -->
            <a href="{{ route('finance.reports.projects') }}" class="group bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-sky-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-700 font-bold text-xl flex items-center justify-center mb-4 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                        📊
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-sky-700 transition-colors">Project Financials</h2>
                    <p class="text-sm text-slate-500 leading-relaxed">View contract values, client payments, approved costs, balances due, and estimated profit across all projects.</p>
                </div>
                <div class="mt-6 text-xs font-bold text-sky-700 flex items-center gap-1 group-hover:gap-2 transition-all">
                    View Report &rarr;
                </div>
            </a>

            <!-- Option 2: Expenses -->
            <a href="{{ route('finance.reports.expenses') }}" class="group bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-sky-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-700 font-bold text-xl flex items-center justify-center mb-4 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                        💸
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-sky-700 transition-colors">Expenses</h2>
                    <p class="text-sm text-slate-500 leading-relaxed">Filter and analyze approved and pending expenses logged against projects, jobs, and pre-project inspections.</p>
                </div>
                <div class="mt-6 text-xs font-bold text-sky-700 flex items-center gap-1 group-hover:gap-2 transition-all">
                    View Report &rarr;
                </div>
            </a>

            <!-- Option 3: Payments -->
            <a href="{{ route('finance.reports.payments') }}" class="group bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-sky-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        💳
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-sky-700 transition-colors">Payments</h2>
                    <p class="text-sm text-slate-500 leading-relaxed">Track money received from clients, payment methods, transaction references, and recording history.</p>
                </div>
                <div class="mt-6 text-xs font-bold text-sky-700 flex items-center gap-1 group-hover:gap-2 transition-all">
                    View Report &rarr;
                </div>
            </a>
        </div>

    </div>
</div>
@endsection
