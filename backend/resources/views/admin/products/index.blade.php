@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-6">
                <div>
                    <h1 class="text-5xl font-bold text-gray-900 mb-2">Products Management</h1>
                    <p class="text-lg text-gray-600">Manage your product catalog and inventory</p>
                </div>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-4 rounded-xl font-semibold transition transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Product
                </a>
            </div>
        </div>

        @php
            $flattened = [];
            $rowId = 1;
            foreach ($solutionProducts as $category) {
                foreach ($category['items'] as $item) {
                    $flattened[] = [
                        'row_id' => $rowId++,
                        'category' => $category['title'] ?? 'Uncategorized',
                        'solution_id' => $category['id'] ?? null,
                        'item' => $item,
                    ];
                }
            }
        @endphp

        @if(count($flattened) > 0)
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-gray-600 text-sm font-medium">Total Products</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ count($flattened) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-gray-600 text-sm font-medium">In Stock</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ collect($flattened)->filter(fn($r) => ($r['item']['stock'] ?? 0) > 0)->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <p class="text-gray-600 text-sm font-medium">Out of Stock</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ collect($flattened)->filter(fn($r) => ($r['item']['stock'] ?? 0) <= 0)->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-gray-600 text-sm font-medium">Visible on Website</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ collect($flattened)->filter(fn($r) => !empty($r['item']['display_on_website']))->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-8 flex flex-col md:flex-row gap-3">
                <input id="productSearch" type="text" placeholder="Search by name, barcode, or category..." class="w-full md:flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select id="websiteFilter" class="w-full md:w-56 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All website status</option>
                    <option value="visible">Visible on website</option>
                    <option value="hidden">Hidden from website</option>
                </select>
                <select id="stockFilter" class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All stock</option>
                    <option value="in-stock">In stock</option>
                    <option value="sold-out">Sold out</option>
                </select>
            </div>

            <!-- Products Grid -->
            <div id="productsGrid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($flattened as $row)
                    @php($item = $row['item'])
                    <div class="product-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden border border-gray-100 group hover:-translate-y-1"
                         data-name="{{ strtolower($item['name'] ?? '') }}"
                         data-barcode="{{ strtolower($item['barcode'] ?? '') }}"
                         data-category="{{ strtolower($row['category'] ?? '') }}"
                         data-website="{{ !empty($item['display_on_website']) ? 'visible' : 'hidden' }}"
                         data-stock="{{ ($item['stock'] ?? 0) > 0 ? 'in-stock' : 'sold-out' }}">
                        <!-- Product Image -->
                        <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-gray-500 text-sm">No image</span>
                                    </div>
                                </div>
                            @endif
                            <!-- Stock Badge -->
                            <div class="absolute top-3 right-3 flex flex-col items-end gap-2">
                                @php($stock = $item['stock'] ?? 0)
                                <span class="px-3 py-1 rounded-full text-xs font-bold @if($stock > 0) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif shadow-md">
                                    {{ $stock > 0 ? $stock . ' in stock' : 'Sold Out' }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold @if(!empty($item['display_on_website'])) bg-indigo-100 text-indigo-800 @else bg-gray-200 text-gray-700 @endif shadow-md website-badge">
                                    {{ !empty($item['display_on_website']) ? 'Website On' : 'Website Off' }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-6">
                            <!-- Name and Category -->
                            <div class="mb-4">
                                <h3 class="text-lg font-bold text-gray-900 line-clamp-2 mb-1">{{ $item['name'] }}</h3>
                                <p class="text-sm text-blue-600 font-medium">{{ $row['category'] }}</p>
                            </div>

                            <!-- Description -->
                            @if(!empty($item['description']))
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $item['description'] }}</p>
                            @endif

                            <!-- Price -->
                            <div class="mb-4 pt-4 border-t border-gray-100">
                                <p class="text-3xl font-bold text-gray-900">{{ $item['price'] ?? '₦0.00' }}</p>
                            </div>

                            <!-- Barcode Section -->
                            @if(!empty($item['barcode']))
                                <div class="mb-4 bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-600 font-medium mb-1">Barcode</p>
                                    <code class="text-sm font-mono text-gray-900 break-all">{{ $item['barcode'] }}</code>
                                </div>
                            @endif

                            <!-- Display on Website Toggle -->
                            <div class="mb-4 bg-gray-50 p-3 rounded-lg flex items-center justify-between">
                                <p class="text-xs text-gray-600 font-medium">Show on Website</p>
                                @if(!empty($item['id']))
                                    <?php 
                                        $isDisplayed = !empty($item['display_on_website']);
                                        $bgClass = $isDisplayed ? 'bg-green-500' : 'bg-gray-300';
                                        $slideClass = $isDisplayed ? 'translate-x-6' : 'translate-x-1';
                                    ?>
                                    <button type="button" class="toggle-display relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $bgClass }}" data-item-id="{{ $item['id'] }}" data-display="{{ $isDisplayed ? 'true' : 'false' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $slideClass }}"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2">
                                @if(!empty($item['id']) && !empty($item['solution_id']))
                                    <!-- Edit Button -->
                                    <a href="{{ route('admin.solutions.items.edit', [$row['solution_id'], $item['id']]) }}" class="block w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold py-2 rounded-lg transition">
                                        ✏️ Edit Product
                                    </a>

                                    <!-- Barcode Actions -->
                                    @if(!empty($item['barcode']))
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('barcode.download', ['solutionItem' => $item['id']]) }}" class="text-center bg-green-50 hover:bg-green-100 text-green-700 text-sm font-semibold py-2 rounded-lg transition">
                                                📥 Download
                                            </a>
                                            <a href="{{ route('barcode.print', ['solutionItem' => $item['id']]) }}" class="text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold py-2 rounded-lg transition" target="_blank">
                                                🖨️ Print
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.solutions.items.destroy', [$row['solution_id'], $item['id']]) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-semibold py-2 rounded-lg transition" onclick="return confirm('Delete this product?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                @else
                                    <p class="text-center text-gray-500 text-sm py-2">View only</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-12 text-center">
                <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h2 class="text-2xl font-bold text-blue-900 mb-2">No Products Yet</h2>
                <p class="text-blue-700 mb-6">Create your first product to get started. Products will appear in the POS system for sales.</p>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create First Product
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.toggle-display').forEach(button => {
    button.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const isCurrentlyDisplayed = this.dataset.display === 'true';
        const newDisplayStatus = !isCurrentlyDisplayed;
        
        // Update UI optimistically
        this.classList.toggle('bg-green-500');
        this.classList.toggle('bg-gray-300');
        this.querySelector('span').classList.toggle('translate-x-6');
        this.querySelector('span').classList.toggle('translate-x-1');
        this.dataset.display = newDisplayStatus ? 'true' : 'false';
        
        // Send update to server
        fetch(`/api/menu-items/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                display_on_website: newDisplayStatus
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to update website visibility');
            const card = this.closest('.product-card');
            if (card) {
                card.dataset.website = newDisplayStatus ? 'visible' : 'hidden';
                const badge = card.querySelector('.website-badge');
                if (badge) {
                    badge.textContent = newDisplayStatus ? 'Website On' : 'Website Off';
                    badge.classList.toggle('bg-indigo-100', newDisplayStatus);
                    badge.classList.toggle('text-indigo-800', newDisplayStatus);
                    badge.classList.toggle('bg-gray-200', !newDisplayStatus);
                    badge.classList.toggle('text-gray-700', !newDisplayStatus);
                }
            }
        })
        .catch(error => {
            console.error('Error toggling product display:', error);
            // Revert UI if request fails
            this.classList.toggle('bg-green-500');
            this.classList.toggle('bg-gray-300');
            this.querySelector('span').classList.toggle('translate-x-6');
            this.querySelector('span').classList.toggle('translate-x-1');
            this.dataset.display = isCurrentlyDisplayed ? 'true' : 'false';
        });
    });
});

const productSearch = document.getElementById('productSearch');
const websiteFilter = document.getElementById('websiteFilter');
const stockFilter = document.getElementById('stockFilter');
const productCards = Array.from(document.querySelectorAll('.product-card'));

const filterProducts = () => {
    const q = (productSearch?.value || '').trim().toLowerCase();
    const website = websiteFilter?.value || 'all';
    const stock = stockFilter?.value || 'all';

    productCards.forEach(card => {
        const text = `${card.dataset.name || ''} ${card.dataset.barcode || ''} ${card.dataset.category || ''}`;
        const matchesText = !q || text.includes(q);
        const matchesWebsite = website === 'all' || card.dataset.website === website;
        const matchesStock = stock === 'all' || card.dataset.stock === stock;
        card.style.display = matchesText && matchesWebsite && matchesStock ? '' : 'none';
    });
};

productSearch?.addEventListener('input', filterProducts);
websiteFilter?.addEventListener('change', filterProducts);
stockFilter?.addEventListener('change', filterProducts);
</script>
@endsection
