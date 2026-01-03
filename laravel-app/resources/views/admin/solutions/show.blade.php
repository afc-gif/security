@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">
                <span class="mr-2">{{ $solution->icon }}</span>{{ $solution->name }}
            </h1>
            <p class="text-gray-600 mt-2">{{ $solution->description }}</p>
        </div>
        <a href="{{ route('admin.solutions.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Back
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('admin.solutions.items.create', $solution) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add New Item
        </a>
    </div>

    <div class="grid gap-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
                <div class="grid grid-cols-4 gap-4 items-start">
                    @if ($item->image)
                        <div>
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-32 object-cover rounded">
                        </div>
                    @else
                        <div class="bg-gray-200 h-32 rounded flex items-center justify-center">
                            <span class="text-gray-500">No Image</span>
                        </div>
                    @endif

                    <div class="col-span-2">
                        <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                        <p class="text-gray-600 mt-1">{{ $item->description }}</p>
                        @if ($item->price)
                            <p class="text-lg font-bold text-green-600 mt-2">R{{ number_format($item->price, 2) }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2">
                        <a href="{{ route('admin.solutions.items.edit', [$solution, $item]) }}" 
                           class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 text-center text-sm">
                            Edit
                        </a>
                        <form action="{{ route('admin.solutions.items.destroy', [$solution, $item]) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No items found. <a href="{{ route('admin.solutions.items.create', $solution) }}" class="underline">Add one</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
