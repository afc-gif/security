@extends('admin.layout')

@section('title', 'Project Finance | ARTSCI Admin Console')

@section('content')
@php
    $projectTitle = $project->title ?: $project->project_code;
    $clientName = $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable';
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6">
            <a href="{{ route('finance.projects.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">Back to Projects</a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-bold">Please check the form.</div>
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
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Project Finance</div>
                    <h1 class="mt-1 text-3xl font-extrabold leading-tight text-gray-950">{{ $projectTitle }}</h1>
                    <div class="mt-2 text-lg font-bold text-gray-800">{{ $clientName }}</div>
                    <div class="mt-3 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-md bg-gray-100 px-2.5 py-1 font-bold text-gray-800">{{ $project->project_code }}</span>
                        <span class="rounded-md bg-gray-100 px-2.5 py-1 font-bold text-gray-800">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    @can(\App\Models\FinancePermission::CREATE)
                        <a href="#add-expense" class="inline-flex min-h-[42px] items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                            + Add Expense
                        </a>
                        <a href="{{ route('finance.material-costs.create', $project) }}" class="inline-flex min-h-[42px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">
                            Add Material
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="grid grid-cols-1 divide-y divide-gray-200 md:grid-cols-3 md:divide-x md:divide-y-0">
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Project Value</div>
                    <div class="mt-2 text-2xl font-extrabold text-gray-950">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Amount Paid</div>
                    <div class="mt-2 text-2xl font-extrabold text-gray-950">-</div>
                    <div class="mt-1 text-xs text-gray-500">Not tracked in current finance tables</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Balance Due</div>
                    <div class="mt-2 text-2xl font-extrabold text-gray-950">-</div>
                    <div class="mt-1 text-xs text-gray-500">Requires payment records</div>
                </div>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="grid grid-cols-1 divide-y divide-gray-200 md:grid-cols-5 md:divide-x md:divide-y-0">
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Expenses</div>
                    <div class="mt-2 text-xl font-extrabold text-gray-950">{{ $financeMoney($summary['approved_expenses']) }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Materials</div>
                    <div class="mt-2 text-xl font-extrabold text-gray-950">{{ $financeMoney($summary['approved_materials']) }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Total Costs</div>
                    <div class="mt-2 text-xl font-extrabold text-gray-950">{{ $financeMoney($summary['approved_cost']) }}</div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Remaining Budget</div>
                    <div class="mt-2 text-xl font-extrabold {{ ($summary['remaining_budget'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $summary['remaining_budget'] === null ? '-' : $financeMoney($summary['remaining_budget']) }}</div>
                    @if($summary['remaining_budget'] !== null)
                        <div class="mt-1 text-xs text-gray-500">Remaining budget {{ $financeMoney($summary['remaining_budget']) }}</div>
                    @endif
                </div>
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Estimated Profit</div>
                    <div class="mt-2 text-xl font-extrabold {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $summary['estimated_profit'] === null ? '-' : $financeMoney($summary['estimated_profit']) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h2 class="text-lg font-extrabold text-gray-950">Expenses</h2>
                    </div>
                    @if($project->financialExpenses->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-600">No project expenses recorded.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($project->financialExpenses->sortByDesc(fn ($expense) => $expense->incurred_on?->getTimestamp() ?? $expense->created_at?->getTimestamp() ?? 0) as $expense)
                                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-gray-950">{{ $expense->category?->name ?? 'Expense' }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $expense->description }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $expense->incurred_on?->format('M j, Y') ?? '-' }} / {{ $expense->submitter?->name ?? 'Finance' }}</div>
                                    </div>
                                    <div class="shrink-0 text-left sm:text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($expense->amount) }}</div>
                                        <span class="mt-2 inline-flex rounded-md px-2 py-1 text-xs font-bold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h2 class="text-lg font-extrabold text-gray-950">Materials</h2>
                    </div>
                    @if($project->financialMaterialCosts->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-600">No material costs recorded.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($project->financialMaterialCosts->sortByDesc(fn ($materialCost) => $materialCost->incurred_on?->getTimestamp() ?? $materialCost->created_at?->getTimestamp() ?? 0) as $materialCost)
                                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-gray-950">{{ $materialCost->material_name }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $materialCost->quantity }} {{ $materialCost->unit ?: 'units' }} at {{ $financeMoney($materialCost->unit_cost) }}</div>
                                    </div>
                                    <div class="shrink-0 text-left sm:text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($materialCost->total_cost) }}</div>
                                        <span class="mt-2 inline-flex rounded-md px-2 py-1 text-xs font-bold {{ $financeStatusClass($materialCost->status) }}">{{ $financeStatusLabel($materialCost->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if($financialDocuments->isNotEmpty())
                    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-5 py-4">
                            <h2 class="text-lg font-extrabold text-gray-950">Documents</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($financialDocuments as $item)
                                @php($document = $item['document'])
                                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="truncate font-extrabold text-gray-950">{{ $document->file_name ?? 'Financial document' }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $item['record_type'] }} / {{ $item['record_label'] }}</div>
                                    </div>
                                    <a href="{{ route('finance.documents.download', $document) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">
                                        Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <div class="space-y-6">
                <section id="add-expense" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-extrabold text-gray-950">Add Expense</h2>
                    <p class="mt-1 text-sm text-gray-600">This expense will be saved to {{ $projectTitle }}.</p>

                    @can(\App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.expenses.store', $project) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label for="finance_expense_category_id" class="block text-sm font-bold text-gray-800">Expense Type</label>
                                <select id="finance_expense_category_id" name="finance_expense_category_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Select type</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('finance_expense_category_id') === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="amount" class="block text-sm font-bold text-gray-800">Amount</label>
                                <input id="amount" name="amount" value="{{ old('amount') }}" type="number" min="0" step="0.01" required placeholder="0.00" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-bold text-gray-800">Description</label>
                                <input id="description" name="description" value="{{ old('description') }}" type="text" maxlength="255" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="incurred_on" class="block text-sm font-bold text-gray-800">Date</label>
                                <input id="incurred_on" name="incurred_on" value="{{ old('incurred_on', now()->toDateString()) }}" type="date" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="receipt" class="block text-sm font-bold text-gray-800">Receipt</label>
                                <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-bold file:text-white">
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                <a href="{{ route('finance.projects.show', $project) }}" class="inline-flex min-h-[42px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">Cancel</a>
                                <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Save Expense</button>
                            </div>
                        </form>
                    @else
                        <div class="mt-4 text-sm text-gray-600">You can view project finance records.</div>
                    @endcan
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-extrabold text-gray-950">Financial Profile</h2>
                    @can($project->financial ? \App\Models\FinancePermission::EDIT : \App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.financial.save', $project) }}" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label for="contract_value" class="block text-sm font-bold text-gray-800">Project Value</label>
                                <input id="contract_value" type="number" name="contract_value" value="{{ old('contract_value', $project->financial?->contract_value) }}" min="0" step="0.01" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="approved_budget" class="block text-sm font-bold text-gray-800">Approved Budget</label>
                                <input id="approved_budget" type="number" name="approved_budget" value="{{ old('approved_budget', $project->financial?->approved_budget) }}" min="0" step="0.01" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="financial_notes" class="block text-sm font-bold text-gray-800">Private Notes</label>
                                <textarea id="financial_notes" name="financial_notes" rows="4" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('financial_notes', $project->financial?->financial_notes) }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex w-full min-h-[42px] items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                                Save Profile
                            </button>
                        </form>
                    @else
                        <div class="mt-4 text-sm text-gray-600">Project value and budget can be viewed here once recorded.</div>
                    @endcan
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
