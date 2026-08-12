@extends('admin.layout')

@section('title', 'Job Finance | ARTSCI')

@section('content')
@php
    $client = $job->jobRequest?->client;
    $jobTitle = $job->title ?: $job->jobRequest?->title ?: 'Job';
    $clientName = $client?->company_name ?: $client?->client_name ?: 'Client unavailable';
    $staffName = $job->claimer?->name ?? 'Unassigned';
    $showExpenseModal = $errors->any();
@endphp

<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <a href="{{ route('finance.jobs.index') }}" class="finance-back-link"><- Back to Jobs</a>

        @if (session('success'))
            <div class="finance-success-alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="finance-error-alert">
                <div class="font-bold">Please check the expense form:</div>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Job Header -->
        <div class="finance-job-header-section">
            <div>
                <h1 class="finance-job-page-title">{{ $jobTitle }}</h1>
                <div class="finance-job-meta-grid">
                    <div class="finance-meta-item">
                        <div class="finance-meta-label">Client</div>
                        <div class="finance-meta-value">{{ $clientName }}</div>
                    </div>
                    <div class="finance-meta-item">
                        <div class="finance-meta-label">Assigned Staff</div>
                        <div class="finance-meta-value">{{ $staffName }}</div>
                    </div>
                    <div class="finance-meta-item">
                        <div class="finance-meta-label">Status</div>
                        <div class="mt-1">
                            <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Spent Card -->
        <div class="finance-total-spent-card">
            <div>
                <div class="finance-total-label">Total Spent</div>
                <div class="finance-total-amount">{{ $financeMoney($summary['total']) }}</div>
                @if($summary['pending_total'] > 0)
                    <div class="finance-total-pending">{{ $financeMoney($summary['pending_total']) }} pending review</div>
                @endif
            </div>
            @can(\App\Models\FinancePermission::CREATE)
                <button type="button" data-expense-modal-open class="finance-btn-add-expense">
                    + Add Expense
                </button>
            @endcan
        </div>

        <!-- Expenses Section -->
        <div class="finance-expenses-section">
            <div class="finance-section-header-simple">
                <h2 class="finance-section-title-simple">Expenses</h2>
                <div class="finance-expense-count">{{ $summary['expense_count'] }} recorded</div>
            </div>

            @if($expenses->isEmpty())
                <div class="finance-empty-state-inline">
                    <div class="finance-empty-text">No expenses recorded yet. Add one to get started.</div>
                </div>
            @else
                <div class="finance-expense-list-simple">
                    @foreach($expenses as $expense)
                        <div class="finance-expense-item">
                            <div class="finance-expense-info">
                                <div class="finance-expense-category">{{ $expense->category?->name ?? 'Expense' }}</div>
                                <div class="finance-expense-description">{{ $expense->description ?: 'No description' }}</div>
                                <div class="finance-expense-meta">
                                    <span class="finance-status-small">{{ $financeStatusLabel($expense->status) }}</span>
                                    <span class="finance-expense-date-simple">{{ $expense->incurred_on?->format('M j, Y') ?? $expense->created_at?->format('M j, Y') ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="finance-expense-amount-section">
                                <div class="finance-expense-amount-display">{{ $financeMoney($expense->amount) }}</div>
                                @if($expense->status === \App\Models\FinancialExpense::STATUS_PENDING)
                                    @can(\App\Models\FinancePermission::DELETE)
                                        <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this pending expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="finance-btn-delete-expense">Delete</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@can(\App\Models\FinancePermission::CREATE)
    <div id="expense-modal" class="{{ $showExpenseModal ? '' : 'hidden' }} fixed inset-0 z-[90]" role="dialog" aria-modal="true">
        <div data-expense-modal-close class="absolute inset-0 bg-gray-950/45"></div>
        <div class="finance-modal-sheet">
            <div class="finance-modal-header">
                <h2 class="finance-modal-title">Add Expense</h2>
                <button type="button" data-expense-modal-close class="finance-modal-close-btn" aria-label="Close">×</button>
            </div>

            <form method="POST" action="{{ route('finance.jobs.expenses.store', $job) }}" enctype="multipart/form-data" class="finance-modal-form">
                @csrf

                <div class="finance-form-group">
                    <label for="finance_expense_category_id" class="finance-form-label">Expense Type</label>
                    <select id="finance_expense_category_id" name="finance_expense_category_id" required class="finance-form-input">
                        <option value="">Select expense type...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('finance_expense_category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="finance-form-group">
                    <label for="amount" class="finance-form-label">Amount (₦)</label>
                    <input id="amount" name="amount" value="{{ old('amount') }}" type="number" min="0" step="0.01" required placeholder="20000" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="description" class="finance-form-label">Description</label>
                    <input id="description" name="description" value="{{ old('description') }}" type="text" maxlength="255" placeholder="What was this for?" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="incurred_on" class="finance-form-label">Date</label>
                    <input id="incurred_on" name="incurred_on" value="{{ old('incurred_on', now()->toDateString()) }}" type="date" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="receipt" class="finance-form-label">Receipt (Optional)</label>
                    <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="finance-form-input-file">
                    <div class="finance-form-help">JPG, PNG, or PDF up to 5MB</div>
                </div>

                <div class="finance-modal-actions">
                    <button type="button" data-expense-modal-close class="finance-btn finance-btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="finance-btn finance-btn-primary">
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
