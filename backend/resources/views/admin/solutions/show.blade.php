@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><span class="mr-2">{{ $solution->icon }}</span>{{ $solution->name }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $solution->description ?: 'No description provided.' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.solutions.items.create', $solution) }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">+ Add Item</a>
                <a href="{{ route('admin.solutions.index') }}" class="inline-flex items-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold transition">Back</a>
            </div>
        </div>

        <!-- Search Bar for Items -->
        <div class="mb-6">
            <div class="relative">
                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input id="itemSearch" type="text" placeholder="Search items by name, description, or barcode..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <!-- Search Bar for Items -->
        <div class="mb-6">
            <div class="relative">
                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input id="itemSearch" type="text" placeholder="Search items by name, description, or barcode..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4">
            @forelse ($items as $item)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                        <div class="md:col-span-2">
                            @if ($item->image)
                                <img src="{{ \App\Support\ImageUrl::url($item->image) }}" alt="{{ $item->name }}" class="w-full h-32 object-cover rounded border border-gray-200">
                            @else
                                <div class="h-32 rounded border border-gray-200 bg-gray-100 flex items-center justify-center text-sm text-gray-500">No Image</div>
                            @endif
                        </div>

                        <div class="md:col-span-8">
                            <h3 class="text-lg font-bold text-gray-900">{{ $item->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $item->description ?: 'No description.' }}</p>
                            <div class="mt-2 text-xs text-gray-500">ID: #{{ $item->id }} | Barcode: {{ $item->barcode ?: '—' }}</div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $item->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->stock > 0 ? 'Stock: ' . $item->stock : 'Sold Out' }}
                                </span>
                                @if(!empty($item->display_on_website))
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Visible on Website</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-700">Hidden from Website</span>
                                @endif
                                @if($item->price !== null)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">₦{{ number_format($item->price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex md:flex-col gap-2">
                                <a href="{{ route('barcode.download-image', $item) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-sm font-semibold">
                                    Download Barcode
                                </a>
                                <a href="{{ route('admin.solutions.items.edit', [$solution, $item]) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">Edit</a>
                                <form action="{{ route('admin.solutions.items.destroy', [$solution, $item]) }}" method="POST" onsubmit="return confirm('Delete this item?');" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm font-semibold">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center text-gray-600">
                    No items found for this category.
                    <div class="mt-3">
                        <a href="{{ route('admin.solutions.items.create', $solution) }}" class="text-blue-600 font-semibold hover:underline">Add first item</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('itemSearch');
        const itemCards = document.querySelectorAll('.grid > div');
        
        if (!searchInput) return;
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            itemCards.forEach(card => {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const description = card.querySelector('p')?.textContent.toLowerCase() || '';
                const barcode = card.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || description.includes(searchTerm) || barcode.includes(searchTerm)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show message if no results and search term is not empty
            const gridContainer = document.querySelector('.grid');
            let noResults = gridContainer?.querySelector('.no-results-message');
            
            if (visibleCount === 0 && searchTerm) {
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'no-results-message col-span-full bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center text-gray-600';
                    noResults.innerHTML = 'No items match your search.';
                    gridContainer?.appendChild(noResults);
                }
            } else if (noResults) {
                noResults.remove();
            }
        });
    });
</script>
@endsection
