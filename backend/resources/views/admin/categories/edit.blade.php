@extends('admin.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit Category</h1>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold mb-2">Name *</label>
            <input type="text" id="name" name="name" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" value="{{ old('name', $category->name) }}" required>
            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-semibold mb-2">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-semibold mb-2">Image</label>
            @if($category->image)
                <div class="mb-2">
                    <img src="{{ \App\Support\ImageUrl::url($category->image) }}" alt="{{ $category->name }}" class="h-20 w-20 object-cover rounded">
                </div>
            @endif
            <input type="file" id="image" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-sm font-semibold mb-2">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" class="w-full border rounded px-3 py-2" value="{{ old('sort_order', $category->sort_order) }}">
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="active" value="1" {{ old('active', $category->active) ? 'checked' : '' }} class="mr-2">
                <span class="text-sm font-semibold">Active</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection
