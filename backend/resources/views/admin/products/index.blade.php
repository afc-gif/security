@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Products</h1>
                </div>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="hidden sm:inline">Add Product</span>
                    <span class="sm:hidden">Add</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

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
            <!-- Compact Stats Bar -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6 flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-gray-900">{{ count($flattened) }}</span>
                    <span class="text-sm text-gray-600">Total Products</span>
                </div>
                <div class="w-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-green-600">{{ collect($flattened)->filter(fn($r) => ($r['item']['stock'] ?? 0) > 0)->count() }}</span>
                    <span class="text-sm text-gray-600">In Stock</span>
                </div>
                <div class="w-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-red-600">{{ collect($flattened)->filter(fn($r) => ($r['item']['stock'] ?? 0) <= 0)->count() }}</span>
                    <span class="text-sm text-gray-600">Out of Stock</span>
                </div>
                <div class="w-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-indigo-600">{{ collect($flattened)->filter(fn($r) => !empty($r['item']['display_on_website']))->count() }}</span>
                    <span class="text-sm text-gray-600">On Website</span>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6">
                <div class="flex flex-col gap-4">
                    <!-- Main Search -->
                    <div class="relative">
                        <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input id="productSearch" type="text" placeholder="Search products by name, barcode, or category..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <!-- Filter Dropdowns -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <select id="websiteFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="all">📱 All website status</option>
                            <option value="visible">✓ Visible on website</option>
                            <option value="hidden">✗ Hidden from website</option>
                        </select>
                        <select id="stockFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="all">📦 All stock status</option>
                            <option value="in-stock">✓ In stock</option>
                            <option value="sold-out">✗ Sold out</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($flattened as $row)
                    @php($item = $row['item'])
                    <div class="product-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all overflow-hidden border border-gray-200 hover:border-blue-300 group"
                         data-name="{{ strtolower($item['name'] ?? '') }}"
                         data-barcode="{{ strtolower($item['barcode'] ?? '') }}"
                         data-category="{{ strtolower($row['category'] ?? '') }}"
                         data-website="{{ !empty($item['display_on_website']) ? 'visible' : 'hidden' }}"
                         data-stock="{{ ($item['stock'] ?? 0) > 0 ? 'in-stock' : 'sold-out' }}">
                        
                        <!-- Product Image -->
                        <div class="relative h-40 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Stock & Website Badges -->
                            <div class="absolute top-2 right-2 flex flex-col items-end gap-1">
                                @php($stock = $item['stock'] ?? 0)
                                <span class="px-2 py-1 rounded text-xs font-semibold @if($stock > 0) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif shadow-sm">
                                    {{ $stock > 0 ? '✓ ' . $stock : '✗ Sold Out' }}
                                </span>
                                <span class="px-2 py-1 rounded text-xs font-semibold @if(!empty($item['display_on_website'])) bg-blue-100 text-blue-800 @else bg-gray-200 text-gray-700 @endif shadow-sm website-badge">
                                    {{ !empty($item['display_on_website']) ? '✓ Web' : '✗ Web' }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4 flex flex-col h-full">
                            <!-- Name & Category -->
                            <div class="mb-3 flex-shrink-0">
                                <h3 class="text-sm font-bold text-gray-900 line-clamp-2 leading-tight mb-1">{{ $item['name'] }}</h3>
                                <p class="text-xs text-blue-600 font-medium">{{ $row['category'] }}</p>
                            </div>

                            <!-- Price -->
                            <div class="mb-3 py-2 border-t border-b border-gray-100 flex-shrink-0">
                                <p class="text-2xl font-bold text-gray-900">{{ $item['price'] ?? '₦0.00' }}</p>
                            </div>

                            <!-- Barcode -->
                            @if(!empty($item['barcode']))
                                <div class="mb-3 bg-gray-50 p-2 rounded text-xs flex-shrink-0">
                                    <p class="text-gray-500 mb-1">Barcode:</p>
                                    <code class="text-gray-900 font-mono break-all text-xs">{{ $item['barcode'] }}</code>
                                </div>
                            @endif

                            <!-- Toggle Switch -->
                            <div class="mb-3 bg-gray-50 p-2 rounded flex items-center justify-between flex-shrink-0">
                                <p class="text-xs text-gray-600 font-medium">Show on Web</p>
                                @if(!empty($item['id']))
                                    <?php 
                                        $isDisplayed = !empty($item['display_on_website']);
                                        $bgClass = $isDisplayed ? 'bg-green-500' : 'bg-gray-300';
                                        $slideClass = $isDisplayed ? 'translate-x-5' : 'translate-x-1';
                                    ?>
                                    <button type="button" class="toggle-display relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $bgClass }}" data-item-id="{{ $item['id'] }}" data-display="{{ $isDisplayed ? 'true' : 'false' }}">
                                        <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ $slideClass }}"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2 flex-1 flex flex-col justify-end">
                                @if(!empty($item['id']))
                                    @php($resolvedSolutionId = $item['solution_id'] ?? $row['solution_id'] ?? null)

                                    @if(!empty($resolvedSolutionId))
                                    <a href="{{ route('admin.solutions.items.edit', [$resolvedSolutionId, $item['id']]) }}" class="block w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold py-2 rounded transition text-sm">
                                        ✏️ Edit
                                    </a>
                                    @endif

                                    @if(!empty($item['barcode']))
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('barcode.download', ['solutionItem' => $item['id']]) }}" class="text-center bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold py-1 rounded transition" title="Download">
                                                📥
                                            </a>
                                            <a href="{{ route('barcode.print', ['solutionItem' => $item['id']]) }}" class="text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold py-1 rounded transition" target="_blank" title="Print">
                                                🖨️
                                            </a>
                                        </div>
                                    @endif

                                    @if(!empty($resolvedSolutionId))
                                        <form action="{{ route('admin.solutions.items.destroy', [$resolvedSolutionId, $item['id']]) }}" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-semibold py-2 rounded transition text-sm" onclick="return confirm('Delete this product?')">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-semibold py-2 rounded transition text-sm delete-menu-item" data-item-id="{{ $item['id'] }}">
                                            🗑️ Delete
                                        </button>
                                    @endif
                                @else
                                    <p class="text-center text-gray-500 text-xs py-2">View only</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h2 class="text-xl font-bold text-gray-900 mb-2">No Products Yet</h2>
                <p class="text-gray-600 mb-6">Get started by creating your first product</p>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
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
        this.querySelector('span').classList.toggle('translate-x-5');
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
                    badge.textContent = newDisplayStatus ? '✓ Web' : '✗ Web';
                    badge.classList.toggle('bg-blue-100', newDisplayStatus);
                    badge.classList.toggle('text-blue-800', newDisplayStatus);
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
            this.querySelector('span').classList.toggle('translate-x-5');
            this.querySelector('span').classList.toggle('translate-x-1');
            this.dataset.display = isCurrentlyDisplayed ? 'true' : 'false';
        });
    });
});

document.querySelectorAll('.delete-menu-item').forEach(button => {
    button.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        if (!itemId || !confirm('Delete this product?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(`/api/menu-items/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to delete product');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting product:', error);
            alert('Could not delete product. Please try again.');
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
