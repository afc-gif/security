@extends('admin.layout')

@section('title', 'Project Finance | ARTSCI')

@section('content')
@php
    $projectTitle = $project->title ?: $project->project_code;
    $clientName = $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable';
@endphp

<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="mb-5">
            <a href="{{ route('finance.projects.index') }}" class="finance-back-link">← Back to Projects</a>
        </div>

        @if (session('success'))
            <div class="finance-success-alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="finance-error-alert">
                <div class="font-bold">Please check the form.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Project Header -->
        <div class="finance-job-header-section">
            <h1 class="finance-job-page-title">{{ $projectTitle }}</h1>
            <div class="finance-job-meta-grid">
                <div class="finance-meta-item">
                    <div class="finance-meta-label">Client</div>
                    <div class="finance-meta-value">{{ $clientName }}</div>
                </div>
                <div class="finance-meta-item">
                    <div class="finance-meta-label">Project Code</div>
                    <div class="finance-meta-value">{{ $project->project_code }}</div>
                </div>
                <div class="finance-meta-item">
                    <div class="finance-meta-label">Status</div>
                    <span class="finance-status">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}</span>
                </div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="finance-project-financial-grid">
            <div class="finance-financial-card">
                <div class="finance-financial-label">Project Value</div>
                <div class="finance-financial-amount">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
            </div>
            <div class="finance-financial-card">
                <div class="finance-financial-label">Amount Paid</div>
                <div class="finance-financial-amount">{{ $financeMoney($summary['total_paid']) }}</div>
            </div>
            <div class="finance-financial-card">
                <div class="finance-financial-label">Balance Due</div>
                <div class="finance-financial-amount">
                    @if($summary['contract_value'] === null)
                        -
                    @elseif(!empty($summary['is_overpaid']))
                        <span style="color: #059669; font-size: 0.85em;">Overpaid ({{ $financeMoney($summary['overpaid_amount']) }})</span>
                    @else
                        {{ $financeMoney($summary['balance_due']) }}
                    @endif
                </div>
            </div>
            <div class="finance-financial-card">
                <div class="finance-financial-label">Total Spent</div>
                <div class="finance-financial-amount">{{ $financeMoney($summary['approved_cost']) }}</div>
            </div>
            <div class="finance-financial-card">
                <div class="finance-financial-label">Estimated Profit</div>
                <div class="finance-financial-amount {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-600' : '' }}">
                    {{ $summary['estimated_profit'] === null ? '-' : $financeMoney($summary['estimated_profit']) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Payments Section -->
                <section class="finance-panel">
                    <div class="finance-section-head">
                        <div>
                            <div class="finance-section-title">Payments</div>
                            <div class="finance-row-meta">Total received: {{ $financeMoney($summary['total_paid']) }}</div>
                        </div>
                        @can(\App\Models\FinancePermission::CREATE)
                            <button type="button" data-payment-modal-open class="finance-btn finance-btn-primary">+ Record Payment</button>
                        @endcan
                    </div>

                    @if($project->payments->isEmpty())
                        <div class="finance-empty-state-inline">
                            <p class="finance-empty-text">No payments recorded yet.</p>
                        </div>
                    @else
                        <div class="finance-expense-list-simple">
                            @foreach($project->payments->sortByDesc('payment_date') as $payment)
                                <div class="finance-payment-item">
                                    <div class="finance-expense-info">
                                        <div class="finance-expense-category">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $payment->payment_method)) }}</div>
                                        @if($payment->reference)
                                            <div class="finance-expense-description">{{ $payment->reference }}</div>
                                        @endif
                                        @if($payment->notes)
                                            <div class="finance-expense-description" style="font-size: 0.85em; color: #64748b;">{{ $payment->notes }}</div>
                                        @endif
                                        <div class="finance-expense-meta">
                                            <span class="finance-expense-date-simple">{{ $payment->payment_date->format('d M Y') }}</span>
                                            @if($payment->documents->isNotEmpty())
                                                @foreach($payment->documents as $doc)
                                                    <a href="{{ route('finance.documents.download', $doc) }}" class="finance-doc-link" target="_blank">📄 Receipt</a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="finance-expense-amount-section">
                                        <div class="finance-payment-amount">{{ $financeMoney($payment->amount) }}</div>
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form method="POST" action="{{ route('finance.projects.payments.destroy', [$project, $payment]) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="finance-btn-delete-expense" onclick="return confirm('Delete this payment?')">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Expenses Section -->
                <section class="finance-panel">
                    <div class="finance-section-head">
                        <div>
                            <div class="finance-section-title">Expenses</div>
                            <div class="finance-row-meta">Total: {{ $financeMoney($summary['approved_expenses']) }}</div>
                        </div>
                        @can(\App\Models\FinancePermission::CREATE)
                            <button type="button" data-expense-modal-open class="finance-btn finance-btn-primary">+ Add Expense</button>
                        @endcan
                    </div>

                    @if($project->financialExpenses->isEmpty())
                        <div class="finance-empty-state-inline">
                            <p class="finance-empty-text">No expenses recorded yet.</p>
                        </div>
                    @else
                        <div class="finance-expense-list-simple">
                            @foreach($project->financialExpenses->sortByDesc(fn ($e) => $e->incurred_on ?? $e->created_at) as $expense)
                                <div class="finance-expense-item">
                                    <div class="finance-expense-info">
                                        <div class="finance-expense-category">{{ $expense->category?->name ?? 'Expense' }}</div>
                                        @if($expense->description)
                                            <div class="finance-expense-description">{{ $expense->description }}</div>
                                        @endif
                                        <div class="finance-expense-meta">
                                            <span class="finance-status-small">{{ $financeStatusLabel($expense->status) }}</span>
                                            <span class="finance-expense-date-simple">{{ ($expense->incurred_on ?? $expense->created_at)->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="finance-expense-amount-section">
                                        <div class="finance-expense-amount-display">{{ $financeMoney($expense->amount) }}</div>
                                        @if($expense->status === 'pending')
                                            @can(\App\Models\FinancePermission::DELETE)
                                                <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="finance-btn-delete-expense" onclick="return confirm('Delete this expense?')">Delete</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Materials Section -->
                <section class="finance-panel">
                    <div class="finance-section-head">
                        <div>
                            <div class="finance-section-title">Materials</div>
                            <div class="finance-row-meta">Total: {{ $financeMoney($summary['approved_materials']) }}</div>
                        </div>
                        @can(\App\Models\FinancePermission::CREATE)
                            <a href="{{ route('finance.material-costs.create', $project) }}" class="finance-btn finance-btn-primary">+ Add Material</a>
                        @endcan
                    </div>

                    @if($project->financialMaterialCosts->isEmpty())
                        <div class="finance-empty-state-inline">
                            <p class="finance-empty-text">No materials recorded yet.</p>
                        </div>
                    @else
                        <div class="finance-expense-list-simple">
                            @foreach($project->financialMaterialCosts->sortByDesc(fn ($m) => $m->incurred_on ?? $m->created_at) as $material)
                                <div class="finance-expense-item">
                                    <div class="finance-expense-info">
                                        <div class="finance-expense-category">{{ $material->material_name }}</div>
                                        <div class="finance-expense-description">{{ $material->quantity }} {{ $material->unit ?: 'units' }} @ {{ $financeMoney($material->unit_cost) }} each</div>
                                        <div class="finance-expense-meta">
                                            <span class="finance-status-small">{{ $financeStatusLabel($material->status) }}</span>
                                            <span class="finance-expense-date-simple">{{ ($material->incurred_on ?? $material->created_at)->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="finance-expense-amount-section">
                                        <div class="finance-expense-amount-display">{{ $financeMoney($material->total_cost) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Project Value Form -->
                <section class="finance-panel p-5">
                    <div class="finance-section-title">Project Value</div>
                    <p class="text-sm finance-muted mt-1">Set the project contract value.</p>

                    @can($project->financial ? \App\Models\FinancePermission::EDIT : \App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.financial.save', $project) }}" class="mt-4 space-y-3">
                            @csrf
                            <div class="finance-field">
                                <label for="contract_value">Value</label>
                                <input id="contract_value" type="number" name="contract_value" value="{{ old('contract_value', $project->financial?->contract_value) }}" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <button type="submit" class="finance-btn finance-btn-secondary w-full">Save</button>
                        </form>
                    @endcan
                </section>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@can(\App\Models\FinancePermission::CREATE)
    <div class="finance-modal-sheet" data-payment-modal>
        <div class="finance-modal-header">
            <h2 class="finance-modal-title">Record Payment</h2>
            <button type="button" class="finance-modal-close-btn" data-payment-modal-close aria-label="Close">×</button>
        </div>

        <form method="POST" action="{{ route('finance.projects.payments.store', $project) }}" enctype="multipart/form-data" class="finance-modal-form">
            @csrf
            <div class="finance-form-group">
                <label for="payment_amount" class="finance-form-label">Amount</label>
                <input id="payment_amount" type="number" name="amount" min="0" step="0.01" required placeholder="0.00">
            </div>
            <div class="finance-form-group">
                <label for="payment_date" class="finance-form-label">Payment Date</label>
                <input id="payment_date" type="date" name="payment_date" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="finance-form-group">
                <label for="payment_method" class="finance-form-label">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select method</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="check">Check</option>
                    <option value="cash">Cash</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="finance-form-group">
                <label for="payment_reference" class="finance-form-label">Reference / Invoice (optional)</label>
                <input id="payment_reference" type="text" name="reference" maxlength="255" placeholder="INV-2026-001">
            </div>
            <div class="finance-form-group">
                <label for="payment_notes" class="finance-form-label">Notes (optional)</label>
                <textarea id="payment_notes" name="notes" rows="3" maxlength="5000" placeholder="Additional notes..."></textarea>
            </div>
            <div class="finance-form-group">
                <label for="payment_receipt" class="finance-form-label">Receipt (optional)</label>
                <input id="payment_receipt" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="finance-form-input-file">
                <div class="finance-form-help">Max 5MB (JPG, PNG, PDF)</div>
            </div>

            <div class="finance-modal-actions">
                <button type="button" class="finance-btn finance-btn-secondary" data-payment-modal-close>Cancel</button>
                <button type="submit" class="finance-btn finance-btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
@endcan

<!-- Expense Modal -->
@can(\App\Models\FinancePermission::CREATE)
    <div class="finance-modal-sheet" data-expense-modal>
        <div class="finance-modal-header">
            <h2 class="finance-modal-title">Add Expense</h2>
            <button type="button" class="finance-modal-close-btn" data-expense-modal-close aria-label="Close">×</button>
        </div>

        <form method="POST" action="{{ route('finance.projects.expenses.store', $project) }}" enctype="multipart/form-data" class="finance-modal-form">
            @csrf
            <div class="finance-form-group">
                <label for="finance_expense_category_id" class="finance-form-label">Expense Type</label>
                <select id="finance_expense_category_id" name="finance_expense_category_id" required>
                    <option value="">Select type</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="finance-form-group">
                <label for="expense_amount" class="finance-form-label">Amount</label>
                <input id="expense_amount" type="number" name="amount" min="0" step="0.01" required placeholder="0.00">
            </div>
            <div class="finance-form-group">
                <label for="expense_description" class="finance-form-label">Description (optional)</label>
                <input id="expense_description" type="text" name="description" maxlength="255" placeholder="What was this expense for?">
            </div>
            <div class="finance-form-group">
                <label for="expense_date" class="finance-form-label">Date</label>
                <input id="expense_date" type="date" name="incurred_on" value="{{ now()->toDateString() }}">
            </div>
            <div class="finance-form-group">
                <label for="expense_receipt" class="finance-form-label">Receipt (optional)</label>
                <input id="expense_receipt" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="finance-form-input-file">
                <div class="finance-form-help">Max 5MB (JPG, PNG, PDF)</div>
            </div>

            <div class="finance-modal-actions">
                <button type="button" class="finance-btn finance-btn-secondary" data-expense-modal-close>Cancel</button>
                <button type="submit" class="finance-btn finance-btn-primary">Add Expense</button>
            </div>
        </form>
    </div>
@endcan

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment Modal
    const paymentModal = document.querySelector('[data-payment-modal]');
    const paymentOpenBtn = document.querySelector('[data-payment-modal-open]');
    const paymentCloseBtns = document.querySelectorAll('[data-payment-modal-close]');

    function openPaymentModal() {
        paymentModal?.showModal?.() || paymentModal?.classList.add('active');
    }

    function closePaymentModal() {
        paymentModal?.close?.() || paymentModal?.classList.remove('active');
    }

    paymentOpenBtn?.addEventListener('click', openPaymentModal);
    paymentCloseBtns.forEach(btn => btn.addEventListener('click', closePaymentModal));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePaymentModal();
    });

    // Expense Modal
    const expenseModal = document.querySelector('[data-expense-modal]');
    const expenseOpenBtn = document.querySelector('[data-expense-modal-open]');
    const expenseCloseBtns = document.querySelectorAll('[data-expense-modal-close]');

    function openExpenseModal() {
        expenseModal?.showModal?.() || expenseModal?.classList.add('active');
    }

    function closeExpenseModal() {
        expenseModal?.close?.() || expenseModal?.classList.remove('active');
    }

    expenseOpenBtn?.addEventListener('click', openExpenseModal);
    expenseCloseBtns.forEach(btn => btn.addEventListener('click', closeExpenseModal));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeExpenseModal();
    });

    // Backdrop close for modals
    document.querySelectorAll('[data-payment-modal], [data-expense-modal]').forEach(modal => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal === paymentModal ? closePaymentModal() : closeExpenseModal();
            }
        });
    });
});
</script>
@endsection
