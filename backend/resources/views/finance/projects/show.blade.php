@extends('admin.layout')

@section('title', 'Project Finance Detail | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->project_code }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $project->title }} · {{ $project->client?->client_name ?? 'Client unavailable' }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                @can(\App\Models\FinancePermission::CREATE)
                    <a href="{{ route('finance.expenses.create', ['context_type' => 'project', 'context_id' => $project->id]) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                        Add Expense
                    </a>
                    <a href="{{ route('finance.material-costs.create', $project) }}" class="inline-flex items-center justify-center bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                        Add Material
                    </a>
                @endcan
                <a href="{{ route('finance.projects.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                    Back
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Contract Value</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['contract_value'] === null ? '—' : $financeMoney($summary['contract_value']) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Budget</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $summary['approved_budget'] === null ? '—' : $financeMoney($summary['approved_budget']) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Cost</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $financeMoney($summary['approved_cost']) }}</div>
                <div class="mt-1 text-sm text-gray-600">Expenses {{ $financeMoney($summary['approved_expenses']) }} · Materials {{ $financeMoney($summary['approved_materials']) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estimated Profit</div>
                <div class="mt-2 text-2xl font-bold {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $summary['estimated_profit'] === null ? '—' : $financeMoney($summary['estimated_profit']) }}</div>
                <div class="mt-1 text-sm {{ $summary['is_over_budget'] ? 'text-red-700 font-semibold' : 'text-gray-600' }}">
                    {{ $summary['remaining_budget'] === null ? ($summary['is_over_budget'] ? 'Over budget' : 'Within budget') : 'Remaining budget ' . $financeMoney($summary['remaining_budget']) }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Financial Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Approved Expenses</div>
                    <div class="text-gray-900 font-semibold">{{ $financeMoney($summary['approved_expenses']) }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Approved Material Costs</div>
                    <div class="text-gray-900 font-semibold">{{ $financeMoney($summary['approved_materials']) }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Remaining Budget</div>
                    <div class="font-semibold {{ ($summary['remaining_budget'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $summary['remaining_budget'] === null ? '—' : $financeMoney($summary['remaining_budget']) }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Budget Status</div>
                    @if($summary['is_over_budget'])
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Over budget</span>
                    @elseif($summary['approved_budget'] === null)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">No budget set</span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Within budget</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Financial Profile</h2>
                    @can($project->financial ? \App\Models\FinancePermission::EDIT : \App\Models\FinancePermission::CREATE)
                        <form method="POST" action="{{ route('finance.projects.financial.save', $project) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="contract_value" class="block text-sm font-medium text-gray-700 mb-1">Contract Value</label>
                                <input id="contract_value" type="number" name="contract_value" value="{{ old('contract_value', $project->financial?->contract_value) }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label for="approved_budget" class="block text-sm font-medium text-gray-700 mb-1">Approved Budget</label>
                                <input id="approved_budget" type="number" name="approved_budget" value="{{ old('approved_budget', $project->financial?->approved_budget) }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label for="financial_notes" class="block text-sm font-medium text-gray-700 mb-1">Private Notes</label>
                                <textarea id="financial_notes" name="financial_notes" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('financial_notes', $project->financial?->financial_notes) }}</textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                                Save Profile
                            </button>
                        </form>
                    @else
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Contract Value</dt>
                                <dd class="text-gray-900">{{ $summary['contract_value'] === null ? '—' : $financeMoney($summary['contract_value']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Budget</dt>
                                <dd class="text-gray-900">{{ $summary['approved_budget'] === null ? '—' : $financeMoney($summary['approved_budget']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Private Notes</dt>
                                <dd class="text-gray-900 whitespace-pre-line">{{ $project->financial?->financial_notes ?: '—' }}</dd>
                            </div>
                        </dl>
                    @endcan
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Expenses</h2>
                    </div>
                    @if($project->financialExpenses->isEmpty())
                        <div class="p-8 text-center text-gray-600">No project expenses recorded.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Submitted By</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Approved By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($project->financialExpenses->sortByDesc(fn ($expense) => $expense->incurred_on?->getTimestamp() ?? $expense->created_at?->getTimestamp() ?? 0) as $expense)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->category?->name ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('finance.expenses.show', $expense) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $expense->description }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($expense->amount) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->incurred_on?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($expense->status) }}">{{ $financeStatusLabel($expense->status) }}</span></td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->submitter?->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $expense->approver?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Material Costs</h2>
                    </div>
                    @if($project->financialMaterialCosts->isEmpty())
                        <div class="p-8 text-center text-gray-600">No project material costs recorded.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Material</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Unit</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Unit Cost</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Total Cost</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Submitted By</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Approved By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($project->financialMaterialCosts->sortByDesc(fn ($materialCost) => $materialCost->incurred_on?->getTimestamp() ?? $materialCost->created_at?->getTimestamp() ?? 0) as $materialCost)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <a href="{{ route('finance.material-costs.show', $materialCost) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $materialCost->material_name }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right whitespace-nowrap">{{ $materialCost->quantity }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $materialCost->unit ?: '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($materialCost->unit_cost) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($materialCost->total_cost) }}</td>
                                            <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($materialCost->status) }}">{{ $financeStatusLabel($materialCost->status) }}</span></td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $materialCost->submitter?->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $materialCost->approver?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Financial Documents</h2>
                    </div>
                    @if($financialDocuments->isEmpty())
                        <div class="p-8 text-center text-gray-600">No private financial documents attached.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($financialDocuments as $item)
                                @php($document = $item['document'])
                                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $document->file_name ?? 'Financial document' }}</div>
                                        <div class="text-sm text-gray-600">{{ $item['record_type'] }} · {{ $item['record_label'] }} · Uploaded by {{ $document->uploader?->name ?? '—' }}</div>
                                    </div>
                                    <a href="{{ route('finance.documents.download', $document) }}" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                                        Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
