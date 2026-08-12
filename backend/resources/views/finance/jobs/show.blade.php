@extends('admin.layout')

@section('title', 'Job Finance | ARTSCI Admin Console')

@section('content')
@php
    $client = $job->jobRequest?->client;
    $jobTitle = $job->title ?: $job->jobRequest?->title ?: 'Job';
    $clientName = $client?->company_name ?: $client?->client_name ?: 'Client unavailable';
    $location = trim(collect([$client?->address, $client?->city_state])->filter()->implode(', '));
    $showExpenseModal = $errors->any();
@endphp

<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="mb-5">
            <a href="{{ route('finance.jobs.index') }}" class="finance-btn finance-btn-secondary">Back to Jobs</a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-bold">Please check the expense form.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="finance-panel mb-5 px-5 py-5">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="finance-eyebrow">Job</div>
                    <h1 class="finance-title !text-3xl">{{ $jobTitle }}</h1>
                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="font-bold text-gray-500">Client</dt>
                            <dd class="mt-1 font-bold text-gray-950">{{ $clientName }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-gray-500">Location</dt>
                            <dd class="mt-1 text-gray-800">{{ $location !== '' ? $location : 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-gray-500">Status</dt>
                            <dd class="mt-1 text-gray-800">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-gray-500">Assigned Staff</dt>
                            <dd class="mt-1 text-gray-800">{{ $job->claimer?->name ?? 'Unassigned' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="finance-stat shrink-0 lg:min-w-[240px]">
                    <div class="finance-stat-label">Total Expenses</div>
                    <div class="finance-stat-value">{{ $financeMoney($summary['total']) }}</div>
                </div>
            </div>
        </section>

        <section class="finance-panel">
            <div class="finance-section-head">
                <div>
                    <h2 class="finance-section-title">Job Expenses</h2>
                    <p class="mt-1 text-sm finance-muted">{{ $summary['expense_count'] }} {{ Illuminate\Support\Str::plural('expense', $summary['expense_count']) }} recorded</p>
                </div>
                @can(\App\Models\FinancePermission::CREATE)
                    <button type="button" data-expense-modal-open class="finance-btn finance-btn-primary">
                        + Add Expense
                    </button>
                @endcan
            </div>

            @if($expenses->isEmpty())
                <div class="px-5 py-12 text-center text-gray-600">No expenses recorded yet.</div>
            @else
                <div class="finance-list">
                    @foreach($expenses as $expense)
                        <div class="finance-row">
                            <div class="min-w-0">
                                <div class="finance-row-title">{{ $expense->category?->name ?? 'Expense' }}</div>
                                <div class="finance-row-meta">{{ $expense->description ?: 'No description' }}</div>
                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs font-semibold text-gray-500">
                                    <span>{{ $expense->incurred_on?->format('M j, Y') ?? $expense->created_at?->format('M j, Y') ?? '-' }}</span>
                                    <span>{{ $expense->submitter?->name ?? 'Finance' }}</span>
                                    <span class="finance-status">{{ $financeStatusLabel($expense->status) }}</span>
                                </div>
                            </div>
                            <div></div>
                            <div></div>
                            <div class="flex shrink-0 items-center justify-between gap-3 sm:flex-col sm:items-end">
                                <div class="text-lg font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                                @if($expense->status === \App\Models\FinancialExpense::STATUS_PENDING)
                                    <div class="flex gap-2">
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this pending expense?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-700 hover:text-red-900">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-4 bg-gray-50 px-5 py-4">
                        <div class="font-extrabold text-gray-950">Total Expenses</div>
                        <div class="text-xl font-extrabold text-gray-950">{{ $financeMoney($summary['total']) }}</div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

@can(\App\Models\FinancePermission::CREATE)
    <div id="expense-modal" class="{{ $showExpenseModal ? '' : 'hidden' }} fixed inset-0 z-[90]" role="dialog" aria-modal="true" aria-labelledby="expense-modal-title">
        <div data-expense-modal-close class="absolute inset-0 bg-gray-950/45"></div>
        <div class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-y-auto rounded-t-lg bg-white p-5 shadow-2xl sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-[min(92vw,520px)] sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-lg">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="expense-modal-title" class="text-xl font-extrabold text-gray-950">Add Expense</h2>
                    <p class="mt-1 text-sm text-gray-600">This expense will be saved to {{ $jobTitle }}.</p>
                </div>
                <button type="button" data-expense-modal-close class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 text-xl font-bold text-gray-700 hover:bg-gray-50" aria-label="Close add expense form">×</button>
            </div>

            <form method="POST" action="{{ route('finance.jobs.expenses.store', $job) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="finance_expense_category_id" class="block text-sm font-bold text-gray-800">Expense Type</label>
                    <select id="finance_expense_category_id" name="finance_expense_category_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Select expense type</option>
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
                    <input id="description" name="description" value="{{ old('description') }}" type="text" maxlength="255" placeholder="Optional description" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
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

                <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" data-expense-modal-close class="inline-flex min-h-[42px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                        Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const modal = document.getElementById('expense-modal');
                if (!modal || modal.dataset.bound === '1') return;

                modal.dataset.bound = '1';
                const openButtons = document.querySelectorAll('[data-expense-modal-open]');
                const closeButtons = modal.querySelectorAll('[data-expense-modal-close]');
                const firstField = modal.querySelector('select, input, button');

                const openModal = () => {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    setTimeout(() => firstField?.focus(), 0);
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                };

                openButtons.forEach((button) => button.addEventListener('click', openModal));
                closeButtons.forEach((button) => button.addEventListener('click', closeModal));
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>
    @endpush
@endcan
@endsection
