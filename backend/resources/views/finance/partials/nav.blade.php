@php
    $financeSection = match (true) {
        request()->routeIs('finance.jobs.*') => 'Jobs',
        request()->routeIs('finance.projects.*') || request()->routeIs('finance.material-costs.*') => 'Projects',
        default => 'Overview',
    };
@endphp

<header class="mb-6 rounded-lg border border-slate-200/80 bg-white/80 px-4 py-3 shadow-sm shadow-slate-200/40 sm:px-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="text-xs font-extrabold uppercase tracking-[0.08em] text-slate-500">Finance / {{ $financeSection }}</div>
        </div>
        <div id="finance-account" class="text-sm text-gray-600 sm:text-right">
            <div class="font-bold text-gray-950">{{ auth()->user()?->name ?? 'Finance user' }}</div>
            <div>Finance</div>
        </div>
    </div>
</header>
