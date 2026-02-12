@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Menu Item</h1>

    <form action="{{ route('admin.menu-items.update', $menuItem) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="category_id" class="block text-sm font-semibold mb-2">Category *</label>
            <select id="category_id" name="category_id" class="w-full border rounded px-3 py-2 @error('category_id') border-red-500 @enderror" required>
                <option value="">Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $menuItem->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold mb-2">Name *</label>
            <input type="text" id="name" name="name" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" value="{{ old('name', $menuItem->name) }}" required>
            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-semibold mb-2">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description', $menuItem->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label for="price" class="block text-sm font-semibold mb-2">Price</label>
            <input type="number" id="price" name="price" step="0.01" class="w-full border rounded px-3 py-2" value="{{ old('price', $menuItem->price) }}">
        </div>

        <div class="mb-4">
            <label for="stock" class="block text-sm font-semibold mb-2">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" class="w-full border-2 border-green-400 rounded px-3 py-2 @error('stock') border-red-500 @enderror" 
                value="{{ old('stock', $menuItem->stock) }}" required placeholder="Enter stock quantity"
                style="background-color: #f0fdf4;">
            <span style="color: #16a34a; font-size: 12px; font-weight: bold; display: block; margin-top: 4px;">⭐ Stock field - required for inventory</span>
            @error('stock')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-semibold mb-2">Image</label>
            @if($menuItem->image)
                <div class="mb-2">
                    <img src="{{ \App\Support\ImageUrl::url($menuItem->image) }}" alt="{{ $menuItem->name }}" class="h-20 w-20 object-cover rounded">
                </div>
            @endif
            <input type="file" id="image" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-sm font-semibold mb-2">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" class="w-full border rounded px-3 py-2" value="{{ old('sort_order', $menuItem->sort_order) }}">
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="available" value="1" {{ old('available', $menuItem->available) ? 'checked' : '' }} class="mr-2">
                <span class="text-sm font-semibold">Available</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('admin.menu-items.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection
