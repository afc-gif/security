@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Solution Category</h1>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.solutions.store') }}" class="space-y-6">
                @csrf

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
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g., SURVEILLANCE">
                </div>

                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji) *</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon') }}" maxlength="10" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g., 📹">
                    <p class="text-xs text-gray-500 mt-1">Use a short emoji/icon marker for this category.</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', 0) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Create Category</button>
                    <a href="{{ route('admin.solutions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
