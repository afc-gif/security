@extends('admin.layout')

@section('title', 'Finance | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Finance</h1>
                <p class="text-sm text-gray-600 mt-1">Private pre-project expense activity for inspections and jobs.</p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Expenses</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($pendingTotal) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Pre-Project</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $approvedCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($approvedTotal) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rejected Expenses</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $rejectedCount }}</div>
                <div class="mt-1 text-sm text-gray-600">Retained in history</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Transportation</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $transportCount }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $financeMoney($transportTotal) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900">Recent Financial Activity</h2>
                <a href="{{ route('finance.expenses.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View all</a>
            </div>
            @if($recentExpenses->isEmpty())
                <div class="p-8 text-center text-gray-600">No pre-project expenses recorded yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Context</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentExpenses as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('finance.expenses.show', $expense) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $financeContextReference($expense) }}</a>
                                        <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $financeContextTitle($expense) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $financeContextClient($expense) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($expense->amount) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
