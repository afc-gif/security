@extends('admin.layout')

@section('title', 'Procurement & Inventory | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Procurement & Inventory</h1>
                <p class="text-xs text-slate-500 mt-1">Manage suppliers, track catalog item stock, and record purchases.</p>
            </div>
            <div class="flex items-center gap-2 mt-4 sm:mt-0">
                <button type="button" onclick="openModal('add-supplier-modal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                    + Add Supplier
                </button>
                <button type="button" onclick="openModal('add-product-modal')" class="bg-sky-600 hover:bg-sky-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                    + Catalog Product
                </button>
                <a href="{{ route('finance.procurements.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                    + Record Purchase
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs flex items-center gap-2">
                <span>✓</span> <strong>{{ session('success') }}</strong>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs flex items-center gap-2">
                <span>✕</span> <strong>{{ session('error') }}</strong>
            </div>
        @endif

        {{-- ── SUMMARY STATS ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl border border-indigo-100 bg-white p-5 shadow-sm min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest text-indigo-600 uppercase">Monthly Procurement Spend</div>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V5"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-800 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $financeMoney($monthlySpend) }}">
                    {{ $financeMoney($monthlySpend) }}
                </div>
                <div class="text-xs text-slate-400 mt-2">Spend for {{ now()->format('F Y') }}</div>
            </div>

            <div class="rounded-xl border border-sky-100 bg-white p-5 shadow-sm min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest text-sky-600 uppercase">Items Purchased This Month</div>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-800 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ number_format($monthlyItemsCount, 2) }}">
                    {{ number_format($monthlyItemsCount, 2) }}
                </div>
                <div class="text-xs text-slate-400 mt-2">Total quantity of products bought</div>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-bold tracking-widest text-emerald-600 uppercase">Active Suppliers</div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-800 tabular-nums whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $activeSuppliersCount }}">
                    {{ $activeSuppliersCount }}
                </div>
                <div class="text-xs text-slate-400 mt-2">Suppliers registered in system</div>
            </div>
        </div>

        {{-- ── TAB CONTROLS ──────────────────────────────────────────────────────── --}}
        <div class="flex border-b border-slate-200 mb-6 gap-2">
            <a href="?tab=procurements" class="px-4 py-2 text-xs font-bold transition-all border-b-2 {{ $activeTab === 'procurements' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                📦 Purchases Ledger
            </a>
            <a href="?tab=suppliers" class="px-4 py-2 text-xs font-bold transition-all border-b-2 {{ $activeTab === 'suppliers' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                🤝 Suppliers
            </a>
            <a href="?tab=products" class="px-4 py-2 text-xs font-bold transition-all border-b-2 {{ $activeTab === 'products' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                📋 Inventory Catalog
            </a>
        </div>

        {{-- ── TAB CONTENT: PROCUREMENTS ────────────────────────────────────────── --}}
        @if($activeTab === 'procurements')
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Purchases</h2>
                <span class="text-xs text-slate-400">Page {{ $procurements->currentPage() }} of {{ $procurements->lastPage() }}</span>
            </div>

            @if($procurements->isEmpty())
                <div class="px-5 py-12 text-center text-slate-400 text-sm">
                    No purchases recorded yet. Click <a href="{{ route('finance.procurements.create') }}" class="text-indigo-600 hover:underline">Record Purchase</a> to add one.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-5 py-2.5 text-left font-semibold">Date</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Supplier</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Product</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Qty</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Unit Cost</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Total Cost</th>
                                <th class="px-5 py-2.5 text-center font-semibold">Document</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($procurements as $proc)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $proc->purchase_date->format('M d, Y') }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-800 whitespace-nowrap">{{ $proc->supplier->name }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $proc->product->name }}</td>
                                    <td class="px-5 py-3 text-right text-slate-800 tabular-nums whitespace-nowrap">{{ number_format($proc->quantity, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-slate-800 tabular-nums whitespace-nowrap">{{ $financeMoney($proc->unit_cost) }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-slate-900 tabular-nums whitespace-nowrap">{{ $financeMoney($proc->total_cost) }}</td>
                                    <td class="px-5 py-3 text-center whitespace-nowrap">
                                        @if($proc->documents->isNotEmpty())
                                            <a href="{{ route('finance.documents.download', $proc->documents->first()) }}" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium hover:underline inline-flex items-center gap-1">
                                                📄 Download
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form action="{{ route('finance.procurements.destroy', $proc) }}" method="POST" class="inline" onsubmit="return confirm('Delete this procurement record? Stock count will be reverted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-900 font-medium">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-300">-</span>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($procurements->hasPages())
                    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50">
                        {{ $procurements->links() }}
                    </div>
                @endif
            @endif
        </div>
        @endif

        {{-- ── TAB CONTENT: SUPPLIERS ────────────────────────────────────────────── --}}
        @if($activeTab === 'suppliers')
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Suppliers Directory</h2>
                <span class="text-xs text-slate-400">Page {{ $suppliers->currentPage() }} of {{ $suppliers->lastPage() }}</span>
            </div>

            @if($suppliers->isEmpty())
                <div class="px-5 py-12 text-center text-slate-400 text-sm">
                    No suppliers found. Click <button type="button" onclick="openModal('add-supplier-modal')" class="text-indigo-600 hover:underline">Add Supplier</button> to register one.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-5 py-2.5 text-left font-semibold">Name</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Contact Person</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Phone</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Email</th>
                                <th class="px-5 py-2.5 text-center font-semibold">Purchases</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($suppliers as $sup)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $sup->name }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $sup->contact_person ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $sup->phone ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $sup->email ?? '-' }}</td>
                                    <td class="px-5 py-3 text-center text-slate-800 font-bold whitespace-nowrap">{{ $sup->procurements_count }}</td>
                                    <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('finance.suppliers.edit', $sup) }}" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form action="{{ route('finance.suppliers.destroy', $sup) }}" method="POST" class="inline" onsubmit="return confirm('Delete supplier {{ $sup->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-900 font-medium">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($suppliers->hasPages())
                    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            @endif
        </div>
        @endif

        {{-- ── TAB CONTENT: PRODUCTS ─────────────────────────────────────────────── --}}
        @if($activeTab === 'products')
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Inventory Catalog</h2>
                <span class="text-xs text-slate-400">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
            </div>

            @if($products->isEmpty())
                <div class="px-5 py-12 text-center text-slate-400 text-sm">
                    No products cataloged in inventory. Click <button type="button" onclick="openModal('add-product-modal')" class="text-sky-600 hover:underline">Catalog Product</button> to add one.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-5 py-2.5 text-left font-semibold">Product Name</th>
                                <th class="px-5 py-2.5 text-left font-semibold">SKU</th>
                                <th class="px-5 py-2.5 text-left font-semibold">Description</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Current Stock Level</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($products as $prod)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $prod->name }}</td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $prod->sku ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-500 max-w-xs truncate" title="{{ $prod->description }}">{{ $prod->description ?? '-' }}</td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $prod->current_stock > 0 ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                            {{ number_format($prod->current_stock, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('finance.products.edit', $prod) }}" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                        @can(\App\Models\FinancePermission::DELETE)
                                            <form action="{{ route('finance.products.destroy', $prod) }}" method="POST" class="inline" onsubmit="return confirm('Delete product {{ $prod->name }} from inventory catalog?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-900 font-medium">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($products->hasPages())
                    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
        @endif

    </div>
</div>

{{-- ── MODAL: ADD SUPPLIER ────────────────────────────────────────────────── --}}
<div id="add-supplier-modal" class="hidden fixed inset-0 z-[90]" role="dialog" aria-modal="true">
    <div onclick="closeModal('add-supplier-modal')" class="absolute inset-0 bg-gray-950/45"></div>
    <div class="finance-modal-sheet max-w-md bg-white rounded-xl shadow-xl overflow-hidden p-6 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full">
        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
            <h2 class="text-lg font-bold text-slate-900">Add New Supplier</h2>
            <button type="button" onclick="closeModal('add-supplier-modal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form method="POST" action="{{ route('finance.suppliers.store') }}" class="space-y-3">
            @csrf
            <div>
                <label for="supplier_name" class="block text-xs font-semibold text-slate-700 mb-1">Supplier Name *</label>
                <input id="supplier_name" type="text" name="name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" required placeholder="e.g. Mr. ThankGod">
            </div>
            <div>
                <label for="supplier_contact" class="block text-xs font-semibold text-slate-700 mb-1">Contact Person</label>
                <input id="supplier_contact" type="text" name="contact_person" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="e.g. ThankGod Emmanuel">
            </div>
            <div>
                <label for="supplier_phone" class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                <input id="supplier_phone" type="text" name="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="e.g. +2348030000000">
            </div>
            <div>
                <label for="supplier_email" class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input id="supplier_email" type="email" name="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="e.g. thankgod@example.com">
            </div>
            <div>
                <label for="supplier_address" class="block text-xs font-semibold text-slate-700 mb-1">Office Address</label>
                <textarea id="supplier_address" name="address" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="Supplier physical address..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeModal('add-supplier-modal')" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: CATALOG PRODUCT ──────────────────────────────────────────────── --}}
<div id="add-product-modal" class="hidden fixed inset-0 z-[90]" role="dialog" aria-modal="true">
    <div onclick="closeModal('add-product-modal')" class="absolute inset-0 bg-gray-950/45"></div>
    <div class="finance-modal-sheet max-w-md bg-white rounded-xl shadow-xl overflow-hidden p-6 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full">
        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
            <h2 class="text-lg font-bold text-slate-900">Add Inventory Catalog Product</h2>
            <button type="button" onclick="closeModal('add-product-modal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form method="POST" action="{{ route('finance.products.store') }}" class="space-y-3">
            @csrf
            <div>
                <label for="prod_name" class="block text-xs font-semibold text-slate-700 mb-1">Product Name *</label>
                <input id="prod_name" type="text" name="name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" required placeholder="e.g. 300W Solar Panel">
            </div>
            <div>
                <label for="prod_sku" class="block text-xs font-semibold text-slate-700 mb-1">SKU / Code (Optional)</label>
                <input id="prod_sku" type="text" name="sku" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="e.g. SLP-300W">
            </div>
            <div>
                <label for="prod_desc" class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                <textarea id="prod_desc" name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs" placeholder="Details about this product..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeModal('add-product-modal')" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm">Catalog Product</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}
</script>
@endsection
