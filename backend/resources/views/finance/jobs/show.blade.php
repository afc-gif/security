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

        <!-- Financial Summary Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Total Received Card -->
            <div class="finance-total-spent-card" style="border-left: 4px solid #059669;">
                <div>
                    <div class="finance-total-label" style="color: #047857;">Money Received</div>
                    <div class="finance-total-amount" style="color: #065f46;">{{ $financeMoney($summary['total_received'] ?? 0) }}</div>
                    <div class="finance-total-pending">{{ $summary['payment_count'] ?? 0 }} transaction(s)</div>
                </div>
                @can(\App\Models\FinancePermission::CREATE)
                    <button type="button" data-payment-modal-open class="finance-btn-add-expense" style="background: #059669; color: #fff;">
                        + Record Money Received
                    </button>
                @endcan
            </div>

            <!-- Total Spent Card -->
            <div class="finance-total-spent-card">
                <div>
                    <div class="finance-total-label">Expenses (Spent)</div>
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
        </div>

        <!-- Money Received Section -->
        <div class="finance-expenses-section mb-6" style="border-top: 2px solid #10b981;">
            <div class="finance-section-header-simple">
                <h2 class="finance-section-title-simple" style="color: #065f46;">Money Received</h2>
                <div class="finance-expense-count">{{ $summary['payment_count'] ?? 0 }} recorded</div>
            </div>

            @if(!isset($payments) || $payments->isEmpty())
                <div class="finance-empty-state-inline">
                    <div class="finance-empty-text">No payments or receipts recorded yet.</div>
                </div>
            @else
                <div class="finance-expense-list-simple">
                    @foreach($payments as $payment)
                        <div class="finance-expense-item" style="border-left: 3px solid #10b981;">
                            <div class="finance-expense-info">
                                <div class="finance-expense-category">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wide">
                                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($payment->payment_type ?? 'payment')) }}
                                    </span>
                                    <span class="text-xs text-gray-500 ml-2">via {{ str_replace('_', ' ', \Illuminate\Support\Str::title($payment->payment_method ?? 'transfer')) }}</span>
                                </div>
                                @if($payment->reference)
                                    <div class="text-xs font-mono text-gray-600 mt-0.5">Ref: {{ $payment->reference }}</div>
                                @endif
                                <div class="finance-expense-description">{{ $payment->notes ?: 'No description' }}</div>
                                <div class="finance-expense-meta">
                                    <span class="text-xs text-gray-500">Recorded by {{ $payment->recorder?->name ?? 'Finance' }}</span>
                                    <span class="finance-expense-date-simple">{{ $payment->payment_date?->format('M j, Y') ?? $payment->created_at?->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="finance-expense-amount-section">
                                <div class="text-lg font-bold text-emerald-700">{{ $financeMoney($payment->amount) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <a href="{{ route('finance.expenses.show', $expense) }}" class="text-[11px] font-semibold text-sky-700 hover:underline">View</a>
                                        @can(\App\Models\FinancePermission::APPROVE)
                                            <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="px-2 py-0.5 text-[10px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded transition-colors">Approve</button>
                                            </form>
                                        @endcan
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this pending expense?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="finance-btn-delete-expense">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
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
                if (modal && modal.dataset.bound !== '1') {
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
                }

                const payModal = document.getElementById('payment-modal');
                if (payModal && payModal.dataset.bound !== '1') {
                    payModal.dataset.bound = '1';
                    const openPayButtons = document.querySelectorAll('[data-payment-modal-open]');
                    const closePayButtons = payModal.querySelectorAll('[data-payment-modal-close]');
                    const firstPayField = payModal.querySelector('input, select, button');

                    const openPayModal = () => {
                        payModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        setTimeout(() => firstPayField?.focus(), 0);
                    };

                    const closePayModal = () => {
                        payModal.classList.add('hidden');
                        document.body.style.overflow = '';
                    };

                    openPayButtons.forEach((button) => button.addEventListener('click', openPayModal));
                    closePayButtons.forEach((button) => button.addEventListener('click', closePayModal));
                }

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
                        if (payModal && !payModal.classList.contains('hidden')) payModal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            })();
        </script>
    @endpush

    <div id="payment-modal" class="hidden fixed inset-0 z-[90]" role="dialog" aria-modal="true">
        <div data-payment-modal-close class="absolute inset-0 bg-gray-950/45"></div>
        <div class="finance-modal-sheet">
            <div class="finance-modal-header" style="border-bottom: 2px solid #059669;">
                <h2 class="finance-modal-title" style="color: #065f46;">Record Money Received</h2>
                <button type="button" data-payment-modal-close class="finance-modal-close-btn" aria-label="Close">×</button>
            </div>

            <form method="POST" action="{{ route('finance.jobs.payments.store', $job) }}" enctype="multipart/form-data" class="finance-modal-form">
                @csrf

                <div class="finance-form-group">
                    <label for="payment_amount" class="finance-form-label">Amount (₦)</label>
                    <input id="payment_amount" name="amount" value="{{ old('amount') }}" type="number" min="0.01" step="0.01" required placeholder="1000000" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="payment_type" class="finance-form-label">Payment Nature / Type</label>
                    <select id="payment_type" name="payment_type" required class="finance-form-input">
                        <option value="deposit">Deposit</option>
                        <option value="part_payment" selected>Part Payment</option>
                        <option value="full_payment">Full Payment</option>
                        <option value="advance">Advance</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="finance-form-group">
                    <label for="payment_method" class="finance-form-label">Payment Method</label>
                    <select id="payment_method" name="payment_method" required class="finance-form-input">
                        <option value="bank_transfer" selected>Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check / Cheque</option>
                        <option value="pos">POS Terminal</option>
                        <option value="online">Online / Card</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="finance-form-group">
                    <label for="payment_date" class="finance-form-label">Date Received</label>
                    <input id="payment_date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" type="date" required class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="reference" class="finance-form-label">Transaction Reference (Optional)</label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" type="text" maxlength="255" placeholder="e.g. TXN-9842184" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="payment_notes" class="finance-form-label">Notes / Description</label>
                    <input id="payment_notes" name="notes" value="{{ old('notes') }}" type="text" maxlength="500" placeholder="e.g. Initial deposit for solar installation" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="payment_receipt" class="finance-form-label">Receipt / Evidence (Optional)</label>
                    <input id="payment_receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="finance-form-input-file">
                    <div class="finance-form-help">JPG, PNG, or PDF up to 5MB</div>
                </div>

                <div class="finance-modal-actions">
                    <button type="button" data-payment-modal-close class="finance-btn finance-btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="finance-btn" style="background: #059669; color: #fff;">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
@endsection
