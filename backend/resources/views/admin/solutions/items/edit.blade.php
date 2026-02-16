@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Item</h1>
        <p class="text-sm text-gray-600 mb-6">Category: <strong>{{ $solution->name }}</strong></p>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form action="{{ route('admin.solutions.items.update', [$solution, $item]) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $item->barcode) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Leave blank to keep/generate">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ old('description', $item->description) }}</textarea>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₦)</label>
                    <input type="number" name="price" id="price" step="0.01" value="{{ old('price', $item->price) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Nigerian Naira only.</p>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock" id="stock" min="0" required value="{{ old('stock', $item->stock) }}" class="w-full px-3 py-2 border-2 border-green-400 rounded-lg bg-green-50">
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Item Image</label>
                    @if ($item->image)
                        <div class="mb-2">
                            <img src="{{ \App\Support\ImageUrl::url($item->image) }}" alt="{{ $item->name }}" class="h-32 rounded border border-gray-200">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $item->sort_order) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="display_on_website" value="1" @checked(old('display_on_website', $item->display_on_website ?? true)) class="w-5 h-5 rounded">
                        Display on website
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Update Item</button>
                    <a href="{{ route('admin.solutions.show', $solution) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
