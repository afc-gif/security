@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Create Menu Item</h1>

    <form action="{{ route('admin.menu-items.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="category_id" class="block text-sm font-semibold mb-2">Category *</label>
            <select id="category_id" name="category_id" class="w-full border rounded px-3 py-2 @error('category_id') border-red-500 @enderror" required>
                <option value="">Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold mb-2">Name *</label>
            <input type="text" id="name" name="name" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-semibold mb-2">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label for="price" class="block text-sm font-semibold mb-2">Price</label>
            <input type="number" id="price" name="price" step="0.01" class="w-full border rounded px-3 py-2" value="{{ old('price') }}">
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-semibold mb-2">Image</label>
            <input type="file" id="image" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-sm font-semibold mb-2">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" class="w-full border rounded px-3 py-2" value="{{ old('sort_order', 0) }}">
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="available" value="1" {{ old('available', true) ? 'checked' : '' }} class="mr-2">
                <span class="text-sm font-semibold">Available</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Create</button>
            <a href="{{ route('admin.menu-items.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection
