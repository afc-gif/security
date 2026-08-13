@extends('admin.layout')

@section('title', 'Create Quotation | ARTSCI Finance')

@section('content')
<div class="finance-page">
    <div class="finance-wrap">
        @include('finance.partials.nav')

        <a href="{{ route('finance.quotations.index') }}" class="finance-back-link"><- Back to Quotations</a>

        <div class="finance-header mb-6">
            <h1 class="finance-title">Create Quotation</h1>
            <p class="finance-subtitle">Select an existing Job to generate an itemized customer quote.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm font-medium">
                <div class="font-bold mb-1">Please correct the following errors:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.quotations.store') }}" id="quotationForm" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
            @csrf

            <!-- Header Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="finance-form-group lg:col-span-2">
                    <label for="job_request_item_id" class="finance-form-label">Job <span class="text-rose-500">*</span></label>
                    <select id="job_request_item_id" name="job_request_item_id" required class="finance-form-input">
                        <option value="">Select an existing Job...</option>
                        @foreach($jobItems as $jobItem)
                            @php
                                $c = $jobItem->jobRequest?->client;
                                $cName = $c?->company_name ?: $c?->client_name ?: 'Client';
                            @endphp
                            <option value="{{ $jobItem->id }}"
                                    data-client-name="{{ $cName }}"
                                    data-client-contact="{{ $c?->contact_person ?? '' }}"
                                    data-client-phone="{{ $c?->phone ?? '' }}"
                                    data-client-email="{{ $c?->email ?? '' }}"
                                    @selected((string) old('job_request_item_id', $selectedJobItemId) === (string) $jobItem->id)>
                                #{{ $jobItem->id }} — {{ $jobItem->title }} [Customer: {{ $cName }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Auto-Display Card -->
                <div class="finance-form-group">
                    <label class="finance-form-label">Customer (Source of Truth)</label>
                    <div id="customerPreviewCard" class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-700 h-[42px] flex flex-col justify-center">
                        <div id="customerNameDisplay" class="font-bold text-slate-900 truncate">Select a job...</div>
                        <div id="customerContactDisplay" class="text-[11px] text-slate-500 truncate hidden"></div>
                    </div>
                </div>

                <div class="finance-form-group lg:col-span-2">
                    <label for="title" class="finance-form-label">Quotation Title / Subject <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" value="{{ old('title') }}" type="text" required placeholder="e.g. CCTV System Supply & Installation" class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="quotation_date" class="finance-form-label">Quotation Date <span class="text-rose-500">*</span></label>
                    <input id="quotation_date" name="quotation_date" value="{{ old('quotation_date', now()->toDateString()) }}" type="date" required class="finance-form-input">
                </div>

                <div class="finance-form-group">
                    <label for="valid_until" class="finance-form-label">Valid Until Date (Optional)</label>
                    <input id="valid_until" name="valid_until" value="{{ old('valid_until') }}" type="date" class="finance-form-input">
                    <span class="text-[11px] text-slate-400 mt-1 block">Leave empty for indefinite validity.</span>
                </div>

                <div class="finance-form-group lg:col-span-2">
                    <label for="inspection_id" class="finance-form-label">Related Inspection (Optional)</label>
                    <select id="inspection_id" name="inspection_id" class="finance-form-input">
                        <option value="">None</option>
                        @foreach($inspections as $insp)
                            <option value="{{ $insp->id }}" @selected((string) old('inspection_id', $selectedInspectionId) === (string) $insp->id)>
                                {{ $insp->inspection_code }} — {{ $insp->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="border-slate-200">

            <!-- Line Items Table -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900">Quotation Line Items</h2>
                    <button type="button" id="addItemBtn" class="finance-btn finance-btn-secondary text-xs px-3 py-1.5">
                        + Add Line Item
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs" id="itemsTable">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                                <th class="p-3 w-12 text-center">#</th>
                                <th class="p-3">Description / Item Name <span class="text-rose-500">*</span></th>
                                <th class="p-3 w-28">Qty <span class="text-rose-500">*</span></th>
                                <th class="p-3 w-36">Unit Price (₦) <span class="text-rose-500">*</span></th>
                                <th class="p-3 w-36 text-right">Line Total (₦)</th>
                                <th class="p-3 w-16 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Calculations Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div class="space-y-4">
                    <div class="finance-form-group">
                        <label for="notes" class="finance-form-label">Notes for Customer (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Thank you for considering ARTSCI Security Systems..." class="finance-form-input">{{ old('notes') }}</textarea>
                    </div>

                    <div class="finance-form-group">
                        <label for="terms" class="finance-form-label">Terms & Conditions (Optional)</label>
                        <textarea id="terms" name="terms" rows="3" placeholder="Payment terms: 70% advance, 30% upon completion..." class="finance-form-input">{{ old('terms') }}</textarea>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-600">Subtotal:</span>
                        <span id="subtotalDisplay" class="font-bold text-slate-900">₦0.00</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label for="discount_amount" class="text-sm font-medium text-slate-600 whitespace-nowrap">Discount (₦):</label>
                        <input id="discount_amount" name="discount_amount" value="{{ old('discount_amount', '0.00') }}" type="number" min="0" step="0.01" class="finance-form-input text-right w-36">
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label for="tax_amount" class="text-sm font-medium text-slate-600 whitespace-nowrap">Tax (₦):</label>
                        <input id="tax_amount" name="tax_amount" value="{{ old('tax_amount', '0.00') }}" type="number" min="0" step="0.01" class="finance-form-input text-right w-36">
                    </div>

                    <hr class="border-slate-300 my-2">

                    <div class="flex items-center justify-between text-base font-extrabold text-emerald-800">
                        <span>Grand Total:</span>
                        <span id="grandTotalDisplay" class="text-lg">₦0.00</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('finance.quotations.index') }}" class="finance-btn finance-btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="finance-btn finance-btn-primary px-6">
                    Save Draft Quotation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const jobSelect = document.getElementById('job_request_item_id');
    const custName = document.getElementById('customerNameDisplay');
    const custContact = document.getElementById('customerContactDisplay');

    function updateCustomerPreview() {
        const opt = jobSelect.options[jobSelect.selectedIndex];
        if (opt && opt.value) {
            const name = opt.getAttribute('data-client-name');
            const contact = opt.getAttribute('data-client-contact');
            const phone = opt.getAttribute('data-client-phone');
            const email = opt.getAttribute('data-client-email');

            custName.textContent = name;
            let info = [contact, phone, email].filter(Boolean).join(' • ');
            if (info) {
                custContact.textContent = info;
                custContact.classList.remove('hidden');
            } else {
                custContact.classList.add('hidden');
            }
        } else {
            custName.textContent = 'Select a job above...';
            custContact.classList.add('hidden');
        }
    }

    jobSelect.addEventListener('change', updateCustomerPreview);
    updateCustomerPreview();

    const container = document.getElementById('itemsContainer');
    const addBtn = document.getElementById('addItemBtn');
    const discountInput = document.getElementById('discount_amount');
    const taxInput = document.getElementById('tax_amount');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    let rowCounter = 0;

    function formatMoney(num) {
        return '₦' + Number(num || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function createRow(description = '', qty = 1, price = 0) {
        rowCounter++;
        const index = rowCounter;
        const tr = document.createElement('tr');
        tr.className = 'item-row hover:bg-slate-50/50';
        tr.dataset.index = index;

        tr.innerHTML = `
            <td class="p-3 text-center font-bold text-slate-400 row-number">1</td>
            <td class="p-3">
                <input type="text" name="items[${index}][description]" value="${description}" required placeholder="Item description / service" class="finance-form-input item-desc">
            </td>
            <td class="p-3">
                <input type="number" name="items[${index}][quantity]" value="${qty}" min="0.01" step="0.01" required class="finance-form-input item-qty text-right">
            </td>
            <td class="p-3">
                <input type="number" name="items[${index}][unit_price]" value="${price}" min="0" step="0.01" required class="finance-form-input item-price text-right">
            </td>
            <td class="p-3 text-right font-bold text-slate-800 item-total">₦0.00</td>
            <td class="p-3 text-center">
                <button type="button" class="remove-row-btn text-rose-600 hover:text-rose-800 font-bold text-lg px-2">×</button>
            </td>
        `;

        container.appendChild(tr);
        bindRowEvents(tr);
        reindexRows();
        recalculateTotals();
    }

    function bindRowEvents(tr) {
        const qtyInput = tr.querySelector('.item-qty');
        const priceInput = tr.querySelector('.item-price');
        const removeBtn = tr.querySelector('.remove-row-btn');

        qtyInput.addEventListener('input', recalculateTotals);
        priceInput.addEventListener('input', recalculateTotals);

        removeBtn.addEventListener('click', () => {
            if (container.querySelectorAll('.item-row').length > 1) {
                tr.remove();
                reindexRows();
                recalculateTotals();
            } else {
                alert('A quotation must have at least one line item.');
            }
        });
    }

    function reindexRows() {
        const rows = container.querySelectorAll('.item-row');
        rows.forEach((tr, i) => {
            tr.querySelector('.row-number').textContent = i + 1;
        });
    }

    function recalculateTotals() {
        let subtotal = 0;
        const rows = container.querySelectorAll('.item-row');

        rows.forEach((tr) => {
            const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
            const price = parseFloat(tr.querySelector('.item-price').value) || 0;
            const total = qty * price;
            subtotal += total;

            tr.querySelector('.item-total').textContent = formatMoney(total);
        });

        const discount = parseFloat(discountInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;
        const actualDiscount = Math.min(subtotal, Math.max(0, discount));
        const grandTotal = Math.max(0, subtotal - actualDiscount + tax);

        subtotalDisplay.textContent = formatMoney(subtotal);
        grandTotalDisplay.textContent = formatMoney(grandTotal);
    }

    addBtn.addEventListener('click', () => createRow());
    discountInput.addEventListener('input', recalculateTotals);
    taxInput.addEventListener('input', recalculateTotals);

    createRow();
})();
</script>
@endpush
@endsection
