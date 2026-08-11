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
                    {{ $summary['remaining_budget'] === null ? 'No budget set' : 'Remaining budget ' . $financeMoney($summary['remaining_budget']) }}
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

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Financial History</h2>
                    </div>
                    @if($activity->isEmpty())
                        <div class="p-8 text-center text-gray-600">No project finance activity recorded yet.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($activity as $item)
                                <a href="{{ $item['url'] }}" class="block px-5 py-4 hover:bg-gray-50">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $item['label'] }}</div>
                                            <div class="text-sm text-gray-600">{{ $item['type'] }} · {{ $item['meta'] }} · {{ $item['date']?->format('d M Y') ?? 'No date' }}</div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-900">{{ $financeMoney($item['amount']) }}</span>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $financeStatusClass($item['status']) }}">{{ $financeStatusLabel($item['status']) }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
