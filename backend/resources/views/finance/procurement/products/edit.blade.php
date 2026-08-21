@extends('admin.layout')

@section('title', 'Edit Inventory Product | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="max-w-xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wide text-sky-700">Inventory Catalog</div>
            <h1 class="mt-1 text-3xl font-extrabold text-slate-900 tracking-tight">Edit Inventory Product</h1>
            <p class="mt-1 text-sm text-slate-500">Update product name, SKU and description catalog details.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800 space-y-1">
                @foreach($errors->all() as $error)
                    <div>✕ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('finance.products.update', $product) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Product Name *</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required placeholder="e.g. 300W Solar Panel">
                </div>

                <div>
                    <label for="sku" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">SKU / Code (Optional)</label>
                    <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="e.g. SLP-300W">
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="Enter product specs, sizing, etc...">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100 mt-5">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                        Save Changes
                    </button>
                    <a href="{{ route('finance.procurements.index', ['tab' => 'products']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-5 py-2.5 rounded-lg text-xs font-bold text-center transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
