@php
    $financeSection = match (true) {
        request()->routeIs('finance.jobs.*') => 'Jobs',
        request()->routeIs('finance.projects.*') || request()->routeIs('finance.material-costs.*') => 'Projects',
        default => 'Overview',
    };
@endphp

<header class="mb-6 rounded-lg border border-gray-200 bg-white px-4 py-4 shadow-sm sm:px-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Finance / {{ $financeSection }}</div>
            <div class="mt-1 text-sm text-gray-600">Manage job expenses and project finances.</div>
        </div>
        <div id="finance-account" class="text-sm text-gray-600 sm:text-right">
            <div class="font-bold text-gray-950">{{ auth()->user()?->name ?? 'Finance user' }}</div>
            <div>Finance</div>
        </div>
    </div>
</header>
