@extends('admin.layout')

@section('title', 'Record Purchase | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-indigo-700">Procurement & Inventory</div>
            <h1 class="mt-1 text-3xl font-extrabold text-slate-900 tracking-tight">Record Purchase</h1>
            <p class="mt-1 text-sm text-slate-500">Record a purchase from a supplier to add stock into the inventory catalog.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800 space-y-1">
                @foreach($errors->all() as $error)
                    <div>✕ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('finance.procurements.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="supplier_id" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Supplier *</label>
                        <select id="supplier_id" name="supplier_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs bg-white text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required>
                            <option value="" disabled selected>-- Select Supplier --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                    {{ $sup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="inventory_product_id" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Product *</label>
                        <select id="inventory_product_id" name="inventory_product_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs bg-white text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required>
                            <option value="" disabled selected>-- Select Product --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" @selected(old('inventory_product_id') == $prod->id)>
                                    {{ $prod->name }} (Current stock: {{ number_format($prod->current_stock, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Quantity Bought *</label>
                        <input id="quantity" type="number" name="quantity" value="{{ old('quantity') }}" min="0.01" step="0.01" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required placeholder="0.00">
                    </div>

                    <div>
                        <label for="unit_cost" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Unit Cost (₦) *</label>
                        <input id="unit_cost" type="number" name="unit_cost" value="{{ old('unit_cost') }}" min="0.00" step="0.01" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required placeholder="0.00">
                    </div>

                    <div>
                        <label for="purchase_date" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Purchase Date *</label>
                        <input id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label for="receipt" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Receipt / Invoice File</label>
                        <input id="receipt" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-xs bg-white focus:border-indigo-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">JPG, PNG or PDF. Max 5MB.</span>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Additional Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="Enter purchase terms, delivery details, serial numbers, etc...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                        Record Purchase &amp; Add Stock
                    </button>
                    <a href="{{ route('finance.procurements.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-5 py-2.5 rounded-lg text-xs font-bold text-center transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
