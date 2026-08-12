@php
    $financeNavItems = [
        ['label' => 'Overview', 'route' => 'finance.dashboard', 'active' => request()->routeIs('finance.dashboard')],
        ['label' => 'Jobs', 'route' => 'finance.jobs.index', 'active' => request()->routeIs('finance.jobs.*')],
        ['label' => 'Projects', 'route' => 'finance.projects.index', 'active' => request()->routeIs('finance.projects.*') || request()->routeIs('finance.material-costs.*')],
    ];
@endphp

<div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 bg-gray-950 px-5 py-4 text-white">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-blue-200">Private Workspace</div>
                <div class="mt-1 text-2xl font-extrabold leading-tight">Finance Panel</div>
                <div class="mt-1 text-sm text-gray-300">Job expenses, project finance, approvals, and private receipts.</div>
            </div>
            <div class="rounded-lg border border-white/15 bg-white/10 px-4 py-3 text-sm">
                <div class="text-gray-300">Signed in as</div>
                <div class="font-bold">{{ auth()->user()?->name ?? 'Finance user' }}</div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-3 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
        <nav class="grid grid-cols-1 gap-2 sm:grid-cols-3" aria-label="Finance navigation">
            @foreach($financeNavItems as $item)
                <a href="{{ route($item['route']) }}" class="inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-bold transition {{ $item['active'] ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <a href="{{ route('finance.jobs.index') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">
            Open Jobs
        </a>
    </div>
</div>
