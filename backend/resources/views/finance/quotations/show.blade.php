@extends('admin.layout')

@section('title', 'Quotation ' . $quotation->quotation_number . ' | ARTSCI Finance')

@section('content')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-document, .print-document * {
        visibility: visible;
    }
    .print-document {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
        box-shadow: none !important;
        border: none !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="finance-page">
    <div class="finance-wrap">
        <div class="no-print">
            @include('finance.partials.nav')

            <a href="{{ route('finance.quotations.index') }}" class="finance-back-link"><- Back to Quotations</a>

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

            <!-- Action Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-500">Status:</span>
                    @php
                        $statusClasses = match($quotation->status) {
                            'draft' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'sent' => 'bg-sky-100 text-sky-800 border-sky-300',
                            'accepted' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                            'expired' => 'bg-slate-100 text-slate-700 border-slate-300',
                            'cancelled' => 'bg-gray-200 text-gray-700 border-gray-300',
                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClasses }}">
                        {{ $quotation->status }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('finance.quotations.download', $quotation) }}" target="_blank" class="finance-btn finance-btn-primary text-xs">
                        ⬇ Download Document
                    </a>
                    <button type="button" onclick="window.print()" class="finance-btn finance-btn-secondary text-xs">
                        🖨 Print / PDF
                    </button>

                    @can(\App\Models\FinancePermission::EDIT)
                        <a href="{{ route('finance.quotations.edit', $quotation) }}" class="finance-btn finance-btn-secondary text-xs">
                            Edit Quotation
                        </a>

                        <!-- Status Action Dropdown/Buttons -->
                        <div class="inline-flex items-center gap-1">
                            @if($quotation->status === 'draft')
                                <form method="POST" action="{{ route('finance.quotations.status', $quotation) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="sent">
                                    <button type="submit" class="finance-btn text-xs bg-sky-600 hover:bg-sky-700 text-white">
                                        Mark Sent
                                    </button>
                                </form>
                            @endif

                            @if(in_array($quotation->status, ['draft', 'sent']))
                                <form method="POST" action="{{ route('finance.quotations.status', $quotation) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="finance-btn text-xs bg-emerald-600 hover:bg-emerald-700 text-white">
                                        Mark Accepted
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('finance.quotations.status', $quotation) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="finance-btn text-xs bg-rose-600 hover:bg-rose-700 text-white">
                                        Mark Rejected
                                    </button>
                                </form>
                            @endif

                            @if($quotation->status !== 'cancelled')
                                <form method="POST" action="{{ route('finance.quotations.status', $quotation) }}" style="display: inline;" onsubmit="return confirm('Cancel this quotation?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="finance-btn text-xs bg-slate-200 hover:bg-slate-300 text-slate-800">
                                        Cancel Quote
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endcan

                    @can(\App\Models\FinancePermission::DELETE)
                        @if($quotation->status !== 'accepted' || !$quotation->payments()->exists())
                            <form method="POST" action="{{ route('finance.quotations.destroy', $quotation) }}" style="display: inline;" onsubmit="return confirm('Permanently delete this quotation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="finance-btn text-xs bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200">
                                    Delete
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Document Sheet -->
        <div class="print-document bg-white rounded-xl border border-slate-200 shadow-sm p-8 max-w-4xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 border-b border-slate-200 pb-6">
                <div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">ARTSCI</div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mt-0.5">Bringing Designing Science</div>
                    <div class="text-xs text-slate-500 mt-2">
                        Port Harcourt, Nigeria<br>
                        Email: support@artsci.com.ng | Web: www.artsci.com.ng
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-wide">QUOTATION</h1>
                    <div class="text-base font-bold font-mono text-sky-700 mt-1">{{ $quotation->quotation_number }}</div>
                    <div class="text-xs text-slate-500 mt-2 space-y-1">
                        <div><strong class="text-slate-700">Date:</strong> {{ $quotation->quotation_date?->format('F j, Y') }}</div>
                        <div><strong class="text-slate-700">Valid Until:</strong> {{ $quotation->valid_until?->format('F j, Y') ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Client & Reference Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-100 text-xs">
                <div>
                    <div class="font-bold text-slate-500 uppercase tracking-wider mb-1">Prepared For</div>
                    <div class="font-bold text-sm text-slate-900">{{ $quotation->client?->company_name ?: $quotation->client?->client_name ?: 'Client' }}</div>
                    @if($quotation->client?->company_name && $quotation->client?->client_name)
                        <div class="text-slate-700 mt-0.5">Attn: {{ $quotation->client->client_name }}</div>
                    @endif
                    @if($quotation->client?->phone)
                        <div class="text-slate-600">Phone: {{ $quotation->client->phone }}</div>
                    @endif
                    @if($quotation->client?->email)
                        <div class="text-slate-600">Email: {{ $quotation->client->email }}</div>
                    @endif
                    @if($quotation->client?->address)
                        <div class="text-slate-600 mt-1">{{ $quotation->client->address }}, {{ $quotation->client->city_state }}</div>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="font-bold text-slate-500 uppercase tracking-wider mb-1">Quote Details & Context</div>
                    <div><strong class="text-slate-700">Subject:</strong> {{ $quotation->title }}</div>
                    @if($quotation->jobRequestItem)
                        <div><strong class="text-slate-700">Related Job Item:</strong> #{{ $quotation->jobRequestItem->id }} - {{ $quotation->jobRequestItem->title }}</div>
                    @endif
                    @if($quotation->inspection)
                        <div><strong class="text-slate-700">Related Inspection:</strong> {{ $quotation->inspection->inspection_code }} ({{ $quotation->inspection->title }})</div>
                    @endif
                    @if($quotation->project)
                        <div><strong class="text-slate-700">Converted Project:</strong> {{ $quotation->project->project_code }} ({{ $quotation->project->title }})</div>
                    @endif
                    <div><strong class="text-slate-700">Created By:</strong> {{ $quotation->creator?->name ?? 'Finance' }}</div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b-2 border-slate-900 text-slate-900 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3 text-center w-10">#</th>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3 text-right w-20">Qty</th>
                            <th class="py-2.5 px-3 text-right w-32">Unit Price</th>
                            <th class="py-2.5 px-3 text-right w-36">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($quotation->items as $index => $item)
                            <tr>
                                <td class="py-3 px-3 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 font-medium text-slate-800">
                                    {{ $item->description }}
                                    @if($item->notes)
                                        <div class="text-[11px] text-slate-500 font-normal mt-0.5">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-medium text-slate-700">{{ (float) $item->quantity }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-700">{{ $financeMoney($item->unit_price) }}</td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900">{{ $financeMoney($item->total_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Totals -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pt-4 border-t border-slate-200">
                <div class="text-xs text-slate-600 space-y-3 max-w-md">
                    @if($quotation->notes)
                        <div>
                            <div class="font-bold text-slate-800 uppercase tracking-wider mb-1">Customer Notes</div>
                            <div class="whitespace-pre-line bg-slate-50 p-2.5 rounded border border-slate-100">{{ $quotation->notes }}</div>
                        </div>
                    @endif

                    @if($quotation->terms)
                        <div>
                            <div class="font-bold text-slate-800 uppercase tracking-wider mb-1">Terms & Conditions</div>
                            <div class="whitespace-pre-line bg-slate-50 p-2.5 rounded border border-slate-100">{{ $quotation->terms }}</div>
                        </div>
                    @endif
                </div>

                <div class="w-full sm:w-72 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-900">{{ $financeMoney($quotation->subtotal) }}</span>
                    </div>

                    @if($quotation->discount_amount > 0)
                        <div class="flex justify-between text-rose-700 font-medium">
                            <span>Discount:</span>
                            <span>-{{ $financeMoney($quotation->discount_amount) }}</span>
                        </div>
                    @endif

                    @if($quotation->tax_amount > 0)
                        <div class="flex justify-between text-slate-600 font-medium">
                            <span>Tax:</span>
                            <span>+{{ $financeMoney($quotation->tax_amount) }}</span>
                        </div>
                    @endif

                    <hr class="border-slate-300 my-2">

                    <div class="flex justify-between text-sm font-black text-emerald-800">
                        <span>Grand Total:</span>
                        <span>{{ $financeMoney($quotation->grand_total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Associated Payments Section (If Any) -->
            @if($quotation->payments->isNotEmpty())
                <div class="pt-6 border-t border-slate-200 no-print">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Associated Customer Payments</h3>
                    <div class="space-y-2">
                        @foreach($quotation->payments as $pay)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs">
                                <div>
                                    <span class="font-bold text-emerald-900 uppercase">{{ str_replace('_', ' ', $pay->payment_type) }}</span>
                                    <span class="text-emerald-700 ml-2">via {{ str_replace('_', ' ', $pay->payment_method) }}</span>
                                    @if($pay->reference)
                                        <span class="font-mono text-emerald-800 ml-2">Ref: {{ $pay->reference }}</span>
                                    @endif
                                    <div class="text-[11px] text-emerald-600 mt-0.5">
                                        Recorded by {{ $pay->recorder?->name ?? 'Finance' }} on {{ $pay->payment_date?->format('M j, Y') }}
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-emerald-800">{{ $financeMoney($pay->amount) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Sign Off Footer -->
            <div class="pt-8 border-t border-slate-200 flex justify-between items-end text-xs text-slate-500">
                <div>
                    <div>Authorized Signature: _______________________</div>
                    <div class="mt-1 font-bold text-slate-700">ARTSCI Management</div>
                </div>
                <div class="text-right">
                    <div>Thank you for your business!</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
