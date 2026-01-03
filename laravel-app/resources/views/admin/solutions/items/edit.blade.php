@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Edit Item: {{ $item->name }}</h1>

    <form action="{{ route('admin.solutions.items.update', [$solution, $item]) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Item Name *</label>
            <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded @error('name') border-red-500 @enderror" 
                   value="{{ old('name', $item->name) }}" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="barcode" class="block text-gray-700 font-bold mb-2">Barcode</label>
            <input type="text" name="barcode" id="barcode" class="w-full px-4 py-2 border rounded @error('barcode') border-red-500 @enderror" 
                   value="{{ old('barcode', $item->barcode) }}" placeholder="Leave blank to keep or auto-generate">
            @error('barcode')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border rounded @error('description') border-red-500 @enderror">{{ old('description', $item->description) }}</textarea>
            @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block text-gray-700 font-bold mb-2">Price (R)</label>
            <input type="number" name="price" id="price" step="0.01" class="w-full px-4 py-2 border rounded @error('price') border-red-500 @enderror" 
                   value="{{ old('price', $item->price) }}">
            @error('price')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="stock" class="block text-gray-700 font-bold mb-2">Stock</label>
            <input type="number" name="stock" id="stock" class="w-full px-4 py-2 border rounded @error('stock') border-red-500 @enderror" 
                   value="{{ old('stock', $item->stock) }}" min="0">
            @error('stock')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="block text-gray-700 font-bold mb-2">Item Image</label>
            @if ($item->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-32 rounded">
                    <p class="text-sm text-gray-600 mt-1">Current image</p>
                </div>
            @endif
            <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border rounded @error('image') border-red-500 @enderror">
            <p class="text-gray-500 text-sm mt-1">Leave empty to keep current image. Max 2MB. Supported: JPEG, PNG, JPG, GIF, WebP</p>
            @error('image')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-gray-700 font-bold mb-2">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" class="w-full px-4 py-2 border rounded @error('sort_order') border-red-500 @enderror" 
                   value="{{ old('sort_order', $item->sort_order) }}">
            @error('sort_order')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Update Item
            </button>
            <a href="{{ route('admin.solutions.show', $solution) }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
