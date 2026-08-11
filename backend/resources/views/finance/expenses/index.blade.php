@extends('admin.layout')

@section('title', 'Finance Expenses | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Pre-Project Expenses</h1>
                <p class="text-sm text-gray-600 mt-1">Inspection and job expenses before project finance begins.</p>
            </div>
            @can(\App\Models\FinancePermission::CREATE)
                <a href="{{ route('finance.expenses.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                    Record Expense
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.expenses.index') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Context</label>
                    <select name="context" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="inspection" @selected(($filters['context'] ?? '') === 'inspection')>Inspection</option>
                        <option value="job" @selected(($filters['context'] ?? '') === 'job')>Job</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Category</label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All</option>
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
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Apply</button>
                <a href="{{ route('finance.expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold">Clear</a>
            </div>
        </form>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            @if($expenses->count() === 0)
                <div class="p-10 text-center text-gray-600">No expenses match the selected filters.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Assigned Staff</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($expenses as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $financeContextReference($expense) }}</div>
                                        <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $financeContextTitle($expense) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $financeContextClient($expense) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $financeAssignedStaff($expense) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($expense->amount) }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span></td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('finance.expenses.show', $expense) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">View</a>
                                        </div>
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
