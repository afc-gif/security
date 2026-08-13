@extends('admin.layout')

@section('title', 'Quotations | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <div class="finance-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="finance-title">Quotations</h1>
                <p class="finance-subtitle">Create, manage, and track formal customer commercial quotes.</p>
            </div>
            @can(\App\Models\FinancePermission::CREATE)
                <a href="{{ route('finance.quotations.create') }}" class="finance-btn finance-btn-primary self-start md:self-auto">
                    + New Quotation
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm font-medium">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Quotes</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total_count'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Drafts</div>
                <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $summary['draft_count'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Sent</div>
                <div class="text-2xl font-extrabold text-sky-700 mt-1">{{ $summary['sent_count'] }}</div>
            </div>
            <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm">
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Accepted Value</div>
                <div class="text-xl font-extrabold text-emerald-800 mt-1">{{ $financeMoney($summary['accepted_value']) }}</div>
                <div class="text-xs text-emerald-600 mt-0.5">{{ $summary['accepted_count'] }} accepted quote(s)</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('finance.quotations.index') }}" class="finance-filter-bar mb-6">
            <div class="finance-filter-content">
                <input 
                    type="search" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by quote #, title, or client..."
                    class="finance-filter-input"
                >
                <select name="status" class="finance-filter-select">
                    <option value="">All Statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <select name="client_id" class="finance-filter-select">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) request('client_id') === (string) $client->id)>
                            {{ $client->company_name ?: $client->client_name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="finance-btn finance-btn-primary">Search</button>
                @if(request('search') || request('status') || request('client_id'))
                    <a href="{{ route('finance.quotations.index') }}" class="finance-btn finance-btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if($quotations->isEmpty())
            <div class="finance-empty-message">
                <div class="finance-empty-icon">📄</div>
                <div class="finance-empty-title">No quotations found</div>
                <div class="text-xs text-slate-500 mt-1">Create a new customer quotation to get started.</div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="p-3.5">Quotation #</th>
                                <th class="p-3.5">Client</th>
                                <th class="p-3.5">Title</th>
                                <th class="p-3.5">Date</th>
                                <th class="p-3.5">Valid Until</th>
                                <th class="p-3.5 text-right">Grand Total</th>
                                <th class="p-3.5 text-center">Status</th>
                                <th class="p-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($quotations as $quotation)
                                @php
                                    $statusClasses = match($quotation->status) {
                                        'draft' => 'bg-amber-100 text-amber-800',
                                        'sent' => 'bg-sky-100 text-sky-800',
                                        'accepted' => 'bg-emerald-100 text-emerald-800',
                                        'rejected' => 'bg-rose-100 text-rose-800',
                                        'expired' => 'bg-slate-100 text-slate-700',
                                        'cancelled' => 'bg-gray-200 text-gray-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3.5 font-bold font-mono text-slate-900">
                                        <a href="{{ route('finance.quotations.show', $quotation) }}" class="hover:underline text-sky-700">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </td>
                                    <td class="p-3.5 font-medium text-slate-800">
                                        {{ $quotation->client?->company_name ?: $quotation->client?->client_name ?: 'N/A' }}
                                    </td>
                                    <td class="p-3.5 text-slate-700 max-w-xs truncate">
                                        {{ $quotation->title }}
                                    </td>
                                    <td class="p-3.5 text-slate-600">
                                        {{ $quotation->quotation_date?->format('M j, Y') }}
                                    </td>
                                    <td class="p-3.5 text-slate-600">
                                        {{ $quotation->valid_until?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="p-3.5 text-right font-bold text-slate-900">
                                        {{ $financeMoney($quotation->grand_total) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusClasses }}">
                                            {{ $quotation->status }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-right space-x-2">
                                        <a href="{{ route('finance.quotations.show', $quotation) }}" class="font-semibold text-sky-700 hover:underline">View</a>
                                        @can(\App\Models\FinancePermission::EDIT)
                                            <a href="{{ route('finance.quotations.edit', $quotation) }}" class="font-semibold text-slate-600 hover:underline">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($quotations->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $quotations->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
