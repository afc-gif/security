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
            <a href="{{ route('finance.projects.index') }}" class="finance-btn finance-btn-secondary">Back to Projects</a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-bold">Please check the form.</div>
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
                    <div class="finance-eyebrow">Project</div>
                    <h1 class="finance-title !text-3xl">{{ $projectTitle }}</h1>
                    <div class="mt-2 text-base font-bold text-gray-800">{{ $clientName }}</div>
                    <div class="mt-3">
                        <span class="finance-status">{{ $project->project_code }}</span>
                        <span class="finance-status">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    @can(\App\Models\FinancePermission::CREATE)
                        <a href="#add-expense" class="finance-btn finance-btn-primary">+ Add Expense</a>
                        <a href="{{ route('finance.material-costs.create', $project) }}" class="finance-btn finance-btn-secondary">+ Add Material Cost</a>
                    @endcan
                </div>
            </div>
        </section>

        <div class="finance-stats">
            <div class="finance-stat">
                <div class="finance-stat-label">Contract Value</div>
                <div class="finance-stat-value">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-label">Approved Costs</div>
                <div class="finance-stat-value">{{ $financeMoney($summary['approved_cost']) }}</div>
            </div>
            <div class="finance-stat">
                <div class="finance-stat-label">Estimated Profit</div>
                <div class="finance-stat-value {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-700' : '' }}">{{ $summary['estimated_profit'] === null ? '-' : $financeMoney($summary['estimated_profit']) }}</div>
            </div>
        </div>

        @if($summary['remaining_budget'] !== null)
            <div class="mb-5 text-sm finance-muted">Remaining budget {{ $financeMoney($summary['remaining_budget']) }}</div>
        @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <section class="finance-panel">
                    <div class="finance-section-head">
                        <div>
                            <div class="finance-section-title">Project Expenses</div>
                            <div class="finance-row-meta">Approved expenses {{ $financeMoney($summary['approved_expenses']) }}</div>
                        </div>
                    </div>
                    @if($project->financialExpenses->isEmpty())
                        <div class="px-5 py-10 text-center finance-muted">No project expenses recorded.</div>
                    @else
                        <div class="finance-list">
                            @foreach($project->financialExpenses->sortByDesc(fn ($expense) => $expense->incurred_on?->getTimestamp() ?? $expense->created_at?->getTimestamp() ?? 0) as $expense)
                                <div class="finance-row">
                                    <div>
                                        <div class="finance-row-title">{{ $expense->category?->name ?? 'Expense' }}</div>
                                        <div class="finance-row-meta">{{ $expense->description ?: 'No description' }}</div>
                                    </div>
                                    <div></div>
                                    <span class="finance-status">{{ $financeStatusLabel($expense->status) }}</span>
                                    <div class="text-right font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="finance-panel">
                    <div class="finance-section-head">
                        <div>
                            <div class="finance-section-title">Materials</div>
                            <div class="finance-row-meta">Approved materials {{ $financeMoney($summary['approved_materials']) }}</div>
                        </div>
                    </div>
                    @if($project->financialMaterialCosts->isEmpty())
                        <div class="px-5 py-10 text-center finance-muted">No material costs recorded.</div>
                    @else
                        <div class="finance-list">
                            @foreach($project->financialMaterialCosts->sortByDesc(fn ($materialCost) => $materialCost->incurred_on?->getTimestamp() ?? $materialCost->created_at?->getTimestamp() ?? 0) as $materialCost)
                                <div class="finance-row">
                                    <div>
                                        <div class="finance-row-title">{{ $materialCost->material_name }}</div>
                                        <div class="finance-row-meta">{{ $materialCost->quantity }} {{ $materialCost->unit ?: 'units' }} at {{ $financeMoney($materialCost->unit_cost) }}</div>
                                    </div>
                                    <div></div>
                                    <span class="finance-status">{{ $financeStatusLabel($materialCost->status) }}</span>
                                    <div class="text-right font-extrabold text-gray-950">{{ $financeMoney($materialCost->total_cost) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <div class="space-y-5">
                <section id="add-expense" class="finance-panel p-5">
                    <div class="finance-section-title">Add Expense</div>
                    <p class="mt-1 text-sm finance-muted">Saved directly to {{ $projectTitle }}.</p>

                    @can(\App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.expenses.store', $project) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                            @csrf
                            <div class="finance-field">
                                <label for="finance_expense_category_id">Expense Type</label>
                                <select id="finance_expense_category_id" name="finance_expense_category_id" required>
                                    <option value="">Select type</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('finance_expense_category_id') === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="finance-field">
                                <label for="amount">Amount</label>
                                <input id="amount" name="amount" value="{{ old('amount') }}" type="number" min="0" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="finance-field">
                                <label for="description">Description</label>
                                <input id="description" name="description" value="{{ old('description') }}" type="text" maxlength="255" placeholder="Optional description">
                            </div>
                            <div class="finance-field">
                                <label for="incurred_on">Date</label>
                                <input id="incurred_on" name="incurred_on" value="{{ old('incurred_on', now()->toDateString()) }}" type="date">
                            </div>
                            <div class="finance-field">
                                <label for="receipt">Receipt</label>
                                <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <button type="submit" class="finance-btn finance-btn-primary w-full">Save Expense</button>
                        </form>
                    @endcan
                </section>

                <section class="finance-panel p-5">
                    <div class="finance-section-title">Financial Profile</div>
                    @can($project->financial ? \App\Models\FinancePermission::EDIT : \App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.financial.save', $project) }}" class="mt-5 space-y-4">
                            @csrf
                            <div class="finance-field">
                                <label for="contract_value">Project Value</label>
                                <input id="contract_value" type="number" name="contract_value" value="{{ old('contract_value', $project->financial?->contract_value) }}" min="0" step="0.01">
                            </div>
                            <div class="finance-field">
                                <label for="approved_budget">Approved Budget</label>
                                <input id="approved_budget" type="number" name="approved_budget" value="{{ old('approved_budget', $project->financial?->approved_budget) }}" min="0" step="0.01">
                            </div>
                            <div class="finance-field">
                                <label for="financial_notes">Private Notes</label>
                                <textarea id="financial_notes" name="financial_notes" rows="4">{{ old('financial_notes', $project->financial?->financial_notes) }}</textarea>
                            </div>
                            <button type="submit" class="finance-btn finance-btn-secondary w-full">Save Profile</button>
                        </form>
                    @endcan
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
