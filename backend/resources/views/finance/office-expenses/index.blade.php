@extends('admin.layout')

@section('title', 'Office Expenses | ARTSCI Finance')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-orange-600 mb-1">Finance · Money Out</div>
                <h1 class="text-3xl font-extrabold text-gray-950">Office &amp; General Expenses</h1>
                <p class="text-sm text-gray-600 mt-1">Company-wide overhead and operating costs not linked to a specific job or project.</p>
            </div>
            @can(\App\Models\FinancePermission::CREATE)
                <a href="{{ route('finance.office-expenses.create') }}" class="inline-flex items-center justify-center rounded-lg bg-orange-600 px-5 py-2.5 font-bold text-white transition hover:bg-orange-700">
                    + Record Office Expense
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Total Records</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($summary['count']) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-green-100 shadow-sm p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-green-600 mb-1">Approved Spend</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $financeMoney($summary['total_approved']) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-yellow-100 shadow-sm p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-yellow-600 mb-1">Pending Review</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $financeMoney($summary['total_pending']) }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('finance.office-expenses.index') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6">
            <div class="mb-3 text-sm font-bold text-gray-950">Filter records</div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Category</label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All</option>
                        @foreach([\App\Models\FinancialExpense::STATUS_PENDING, \App\Models\FinancialExpense::STATUS_APPROVED, \App\Models\FinancialExpense::STATUS_REJECTED] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $financeStatusLabel($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Apply</button>
                    <a href="{{ route('finance.office-expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold">Clear</a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            @if($expenses->count() === 0)
                <div class="p-10 text-center text-gray-600">No office expenses match the selected filters.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Payment Method</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Recorded By</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($expenses as $expense)
                                <tr class="hover:bg-orange-50/30">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $expense->incurred_on?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $expense->category?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 max-w-[240px] truncate">
                                        {{ $expense->description }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $expense->payment_method ? ucwords(str_replace('_', ' ', $expense->payment_method)) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">
                                        {{ $financeMoney($expense->amount) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $expense->submitter?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('finance.office-expenses.show', $expense) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-orange-50 text-orange-700 hover:bg-orange-100 text-sm font-semibold">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
