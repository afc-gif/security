@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Edit Solution Category</h1>

    <form action="{{ route('admin.solutions.update', $solution) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Name *</label>
            <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded @error('name') border-red-500 @enderror" 
                   value="{{ old('name', $solution->name) }}" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="icon" class="block text-gray-700 font-bold mb-2">Icon (emoji) *</label>
            <input type="text" name="icon" id="icon" class="w-full px-4 py-2 border rounded @error('icon') border-red-500 @enderror" 
                   value="{{ old('icon', $solution->icon) }}" placeholder="e.g. 📹" maxlength="10" required>
            @error('icon')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border rounded @error('description') border-red-500 @enderror">{{ old('description', $solution->description) }}</textarea>
            @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="sort_order" class="block text-gray-700 font-bold mb-2">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" class="w-full px-4 py-2 border rounded @error('sort_order') border-red-500 @enderror" 
                   value="{{ old('sort_order', $solution->sort_order) }}">
            @error('sort_order')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Update Solution
            </button>
            <a href="{{ route('admin.solutions.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
