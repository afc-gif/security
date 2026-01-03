@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Solutions Management</h1>
        <a href="{{ route('admin.solutions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add New Solution
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4">
        @forelse ($solutions as $solution)
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold">
                            <span class="mr-2">{{ $solution->icon }}</span>{{ $solution->name }}
                        </h2>
                        <p class="text-gray-600 mt-2">{{ $solution->description }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            <strong>Items:</strong> {{ $solution->items->count() }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.solutions.show', $solution) }}" 
                           class="bg-gray-500 text-white px-3 py-2 rounded hover:bg-gray-600">
                            View Items
                        </a>
                        <a href="{{ route('admin.solutions.edit', $solution) }}" 
                           class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600">
                            Edit
                        </a>
                        <form action="{{ route('admin.solutions.destroy', $solution) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No solutions found. <a href="{{ route('admin.solutions.create') }}" class="underline">Create one</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
