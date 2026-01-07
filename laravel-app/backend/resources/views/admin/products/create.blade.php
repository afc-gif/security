@extends('admin.layout')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Add New Product</h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <select id="category" name="category" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category') border-red-500 @enderror">
                        <option value="">-- Select a Category --</option>
                        @forelse($categories as $cat)
                            <option value="{{ $cat->name }}" @if(old('category') === $cat->name) selected @endif>
                                {{ $cat->icon ?? '' }} {{ $cat->name }}
                            </option>
                        @empty
                            <option value="" disabled>No categories available</option>
                        @endforelse
                    </select>
                    <p class="text-gray-600 text-sm mt-1">Select from the main solution categories</p>
                    @error('category')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                </div>

                <!-- Price & Stock Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror">
                        @error('price')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock *</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('stock') border-red-500 @enderror">
                        @error('stock')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                </div>

                <!-- Image -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('image') border-red-500 @enderror">
                    <p class="text-gray-600 text-sm mt-1">JPG, PNG or GIF (max 2MB)</p>
                    @error('image')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        Create Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
