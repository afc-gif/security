@extends('admin.layout')

@section('title', 'Office Expense Details | ARTSCI Finance')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-orange-600 mb-1">Office Expense</div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $expense->category?->name ?? 'Office Expense' }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $expense->description }}</p>
            </div>
            <a href="{{ route('finance.office-expenses.index') }}"
               class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                ← Back to Office Expenses
            </a>
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

        {{-- Main Detail Card --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Amount</div>
                    <div class="text-4xl font-bold text-gray-900">{{ $financeMoney($expense->amount) }}</div>
                </div>
                <span class="inline-flex items-center px-3 py-1.5 rounded text-sm font-semibold {{ $financeStatusClass($expense->status) }}">
                    {{ $financeStatusLabel($expense->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Category</div>
                    <div class="text-gray-900 font-semibold">{{ $expense->category?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Date Incurred</div>
                    <div class="text-gray-900">{{ $expense->incurred_on?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Payment Method</div>
                    <div class="text-gray-900">{{ $expense->payment_method ? ucwords(str_replace('_', ' ', $expense->payment_method)) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Reference / Transaction ID</div>
                    <div class="text-gray-900 font-mono">{{ $expense->reference ?: '—' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Description</div>
                    <div class="text-gray-900">{{ $expense->description }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Recorded By</div>
                    <div class="text-gray-900">{{ $expense->submitter?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Approved / Rejected By</div>
                    <div class="text-gray-900">{{ $expense->approver?->name ?? '—' }}</div>
                    <div class="text-sm text-gray-500">{{ $expense->approved_at?->format('d M Y H:i') ?? '' }}</div>
                </div>
                @if($expense->notes)
                    <div class="md:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Notes</div>
                        <div class="text-gray-900 whitespace-pre-line">{{ $expense->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Documents</h2>
            @if($expense->documents->isEmpty())
                <div class="text-gray-600">No receipt or document attached.</div>
            @else
                <div class="grid grid-cols-1 gap-3">
                    @foreach($expense->documents as $document)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $document->file_name ?? 'Document' }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $document->file_type ?: 'File' }}
                                    @if($document->file_size)
                                        · {{ number_format($document->file_size / 1024, 1) }} KB
                                    @endif
                                    · Uploaded by {{ $document->uploader?->name ?? '—' }}
                                </div>
                            </div>
                            <a href="{{ route('finance.documents.download', $document) }}"
                               class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Actions</h2>
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                @if($expense->status === \App\Models\FinancialExpense::STATUS_PENDING)
                    @can(\App\Models\FinancePermission::EDIT)
                        <a href="{{ route('finance.office-expenses.edit', $expense) }}"
                           class="inline-flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                            Edit Pending Expense
                        </a>
                    @endcan
                    @can(\App\Models\FinancePermission::APPROVE)
                        <form method="POST" action="{{ route('finance.office-expenses.approve', $expense) }}">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('finance.office-expenses.reject', $expense) }}" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Optional rejection note"
                                   class="border border-gray-300 rounded-lg px-3 py-2 min-w-[200px]">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                                Reject
                            </button>
                        </form>
                    @endcan
                    @can(\App\Models\FinancePermission::DELETE)
                        <form method="POST" action="{{ route('finance.office-expenses.destroy', $expense) }}"
                              onsubmit="return confirm('Permanently delete this office expense?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-2.5 rounded-lg font-semibold">
                                Delete Pending Expense
                            </button>
                        </form>
                    @endcan
                @else
                    @if(auth()->user()?->isSuperAdmin())
                        @can(\App\Models\FinancePermission::EDIT)
                            <a href="{{ route('finance.office-expenses.edit', $expense) }}"
                               class="inline-flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                                Edit Approved Expense
                            </a>
                        @endcan
                    @else
                        <div class="text-gray-600">Approved and rejected records are preserved and cannot be edited.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
