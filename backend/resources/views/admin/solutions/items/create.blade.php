@extends('admin.layout')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Add Item to {{ $solution->name }}</h1>

    <form action="{{ route('admin.solutions.items.store', $solution) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Item Name *</label>
            <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded @error('name') border-red-500 @enderror" 
                   value="{{ old('name') }}" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="barcode" class="block text-gray-700 font-bold mb-2">Barcode</label>
            <input type="text" name="barcode" id="barcode" class="w-full px-4 py-2 border rounded @error('barcode') border-red-500 @enderror" 
                   value="{{ old('barcode') }}" placeholder="Leave blank to auto-generate a unique code">
            @error('barcode')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border rounded @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block text-gray-700 font-bold mb-2">Price (R)</label>
            <input type="number" name="price" id="price" step="0.01" class="w-full px-4 py-2 border rounded @error('price') border-red-500 @enderror" 
                   value="{{ old('price') }}">
            @error('price')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="stock" class="block text-gray-700 font-bold mb-2">Stock Quantity *</label>
            <input type="number" name="stock" id="stock" min="0" required 
                   class="w-full px-4 py-2 border-2 border-green-400 rounded @error('stock') border-red-500 @enderror"
                   style="background-color: #f0fdf4;"
                   value="{{ old('stock', 0) }}" placeholder="Enter stock quantity">
            <span style="color: #16a34a; font-size: 12px; font-weight: bold; display: block; margin-top: 4px;">⭐ Stock quantity - required for inventory</span>
            @error('stock')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="block text-gray-700 font-bold mb-2">Item Image</label>
            <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border rounded @error('image') border-red-500 @enderror">
            <p class="text-gray-500 text-sm mt-1">Max 2MB. Supported: JPEG, PNG, JPG, GIF, WebP</p>
            @error('image')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-gray-700 font-bold mb-2">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" class="w-full px-4 py-2 border rounded @error('sort_order') border-red-500 @enderror" 
                   value="{{ old('sort_order', 0) }}">
            @error('sort_order')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="display_on_website" class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="display_on_website" id="display_on_website" value="1" 
                       @if(old('display_on_website', true)) checked @endif 
                       class="w-5 h-5 rounded">
                <span class="text-gray-700 font-bold">Display on Website</span>
            </label>
            <p class="text-gray-500 text-sm mt-2">Uncheck to hide this product from the public website (will remain available in POS)</p>
            @error('display_on_website')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Add Item
            </button>
            <a href="{{ route('admin.solutions.show', $solution) }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
