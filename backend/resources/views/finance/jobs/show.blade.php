@extends('admin.layout')

@section('title', 'Job Finance | ARTSCI Admin Console')

@section('content')
@php
    $client = $job->jobRequest?->client;
    $jobTitle = $job->title ?: $job->jobRequest?->title ?: 'Job';
    $clientName = $client?->company_name ?: $client?->client_name ?: 'Client unavailable';
    $location = trim(collect([$client?->address, $client?->city_state])->filter()->implode(', '));
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6">
            <a href="{{ route('finance.jobs.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">Back to Jobs</a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-bold">Please check the expense form.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Job Finance</div>
                    <h1 class="mt-1 text-3xl font-extrabold leading-tight text-gray-950">{{ $jobTitle }}</h1>
                    <div class="mt-2 text-lg font-bold text-gray-800">{{ $clientName }}</div>
                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <span class="font-bold text-gray-800">Assigned to:</span>
                            {{ $job->claimer?->name ?? 'Unassigned' }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-800">Status:</span>
                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-800">Date:</span>
                            {{ $job->created_at?->format('M j, Y') ?? '-' }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-800">Location:</span>
                            {{ $location !== '' ? $location : 'Not recorded' }}
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 lg:min-w-[260px]">
                    <div class="text-sm font-bold text-gray-600">Total Expenses</div>
                    <div class="mt-2 text-3xl font-extrabold text-gray-950">{{ $financeMoney($summary['approved_total']) }}</div>
                    <div class="mt-1 text-xs font-semibold text-gray-500">Approved records only</div>
                    @if($summary['pending_total'] > 0)
                        <div class="mt-3 rounded-md bg-yellow-50 px-3 py-2 text-sm font-bold text-yellow-800">
                            Pending: {{ $financeMoney($summary['pending_total']) }}
                        </div>
                    @endif
                    @can(\App\Models\FinancePermission::CREATE)
                        <a href="#add-expense" class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                            + Add Expense
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-950">Expenses</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $summary['expense_count'] }} {{ Illuminate\Support\Str::plural('record', $summary['expense_count']) }} attached to this job</p>
                    </div>
                </div>

                @if($expenses->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <div class="text-lg font-extrabold text-gray-950">No expenses recorded yet</div>
                        <p class="mt-2 text-sm text-gray-600">Use the Add Expense form to record spending for this job.</p>
                    </div>
                @else
                    <div class="hidden lg:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Expense</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Date</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Submitted by</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Status</th>
                                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($expenses as $expense)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-4">
                                            <div class="font-extrabold text-gray-950">{{ $expense->category?->name ?? 'Expense' }}</div>
                                            <div class="mt-1 text-sm text-gray-600">{{ $expense->description }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-700">{{ $expense->incurred_on?->format('M j, Y') ?? $expense->created_at?->format('M j, Y') ?? '-' }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-700">{{ $expense->submitter?->name ?? 'Finance' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-bold {{ $financeStatusClass($expense->status) }}">
                                                {{ $financeStatusLabel($expense->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-gray-100 lg:hidden">
                        @foreach($expenses as $expense)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-gray-950">{{ $expense->category?->name ?? 'Expense' }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $expense->description }}</div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ $expense->incurred_on?->format('M j, Y') ?? $expense->created_at?->format('M j, Y') ?? '-' }}
                                            / {{ $expense->submitter?->name ?? 'Finance' }}
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                                        <span class="mt-2 inline-flex rounded-md px-2 py-1 text-xs font-bold {{ $financeStatusClass($expense->status) }}">
                                            {{ $financeStatusLabel($expense->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @can(\App\Models\FinancePermission::CREATE)
                <div id="add-expense" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-extrabold text-gray-950">Add Expense</h2>
                    <p class="mt-1 text-sm text-gray-600">This expense will be saved to {{ $jobTitle }}.</p>

                    <form method="POST" action="{{ route('finance.jobs.expenses.store', $job) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="finance_expense_category_id" class="block text-sm font-bold text-gray-800">Category</label>
                            <select id="finance_expense_category_id" name="finance_expense_category_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('finance_expense_category_id') === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-bold text-gray-800">Amount</label>
                            <input id="amount" name="amount" value="{{ old('amount') }}" type="number" min="0" step="0.01" required placeholder="0.00" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-800">Description</label>
                            <input id="description" name="description" value="{{ old('description') }}" type="text" maxlength="255" required placeholder="What was spent?" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div>
                            <label for="incurred_on" class="block text-sm font-bold text-gray-800">Date</label>
                            <input id="incurred_on" name="incurred_on" value="{{ old('incurred_on', now()->toDateString()) }}" type="date" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div>
                            <label for="receipt" class="block text-sm font-bold text-gray-800">Receipt</label>
                            <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-bold file:text-white">
                            <p class="mt-1 text-xs text-gray-500">Optional. JPG, PNG, or PDF up to 5MB.</p>
                        </div>

                        <button type="submit" class="inline-flex w-full min-h-[44px] items-center justify-center rounded-md bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                            Save Expense
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
