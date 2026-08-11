@php
    $financeNavItems = [
        ['label' => 'Dashboard', 'route' => 'finance.dashboard', 'active' => request()->routeIs('finance.dashboard')],
        ['label' => 'Pre-Project Expenses', 'route' => 'finance.expenses.index', 'active' => request()->routeIs('finance.expenses.*')],
        ['label' => 'Project Finance', 'route' => 'finance.projects.index', 'active' => request()->routeIs('finance.projects.*') || request()->routeIs('finance.material-costs.*')],
    ];
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <nav class="flex flex-col sm:flex-row gap-2" aria-label="Finance navigation">
            @foreach($financeNavItems as $item)
                <a href="{{ route($item['route']) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold transition {{ $item['active'] ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex flex-col sm:flex-row gap-2">
            @can(\App\Models\FinancePermission::CREATE)
                <a href="{{ route('finance.expenses.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    Record Expense
                </a>
            @endcan
        </div>
    </div>
</div>
