@extends('admin.layout')

@section('content')
<style>
/* Keep product card actions visible on admin/products regardless of global theme CSS */
#productsGrid .product-card {
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
}

#productsGrid .product-card > div {
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
}

#productsGrid .product-card .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
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
                    <div class="product-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 hover:border-blue-300 group"
                         data-name="{{ strtolower($item['name'] ?? '') }}"
                         data-barcode="{{ strtolower($item['barcode'] ?? '') }}"
                         data-category="{{ strtolower($row['category'] ?? '') }}"
                         data-website="{{ !empty($item['display_on_website']) ? 'visible' : 'hidden' }}"
                         data-stock="{{ !empty($item['is_sold_out']) || ($item['stock'] ?? 0) <= 0 ? 'sold-out' : 'in-stock' }}"
                         data-sold-out="{{ !empty($item['is_sold_out']) ? 'true' : 'false' }}">
                        
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
                                <span class="px-2 py-1 rounded text-xs font-semibold stock-badge @if(!empty($item['is_sold_out']) || $stock <= 0) bg-red-100 text-red-800 @else bg-green-100 text-green-800 @endif shadow-sm">
                                    {{ !empty($item['is_sold_out']) || $stock <= 0 ? '✗ Sold Out' : '✓ ' . $stock }}
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
                                @php($itemId = $item['id'] ?? null)
                                @php($resolvedSolutionId = $item['solution_id'] ?? $row['solution_id'] ?? null)
                                @php($resolvedProductId = $item['product_id'] ?? null)
                                @php($hasItem = !is_null($itemId))
                                @php($editUrl = !empty($resolvedSolutionId) && $hasItem
                                    ? route('admin.solutions.items.edit', [$resolvedSolutionId, $itemId])
                                    : (!empty($resolvedProductId) ? route('admin.products.edit', $resolvedProductId) : null))

                                {{-- Edit Button: Always visible, enabled if editUrl exists --}}
                                @if(!empty($editUrl))
                                    <a href="{{ $editUrl }}" class="block w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold py-2 rounded transition text-sm">
                                        ✏️ Edit
                                    </a>
                                @else
                                    <button type="button" class="w-full text-center bg-blue-50 text-blue-300 font-semibold py-2 rounded text-sm cursor-not-allowed opacity-50" disabled>
                                        ✏️ Edit
                                    </button>
                                @endif

                                {{-- Action Buttons Row --}}
                                <div class="grid grid-cols-2 gap-2">
                                    {{-- Mark Sold Out Button --}}
                                    <button type="button" class="text-center bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold py-1 rounded transition toggle-sold-out" data-item-id="{{ $itemId }}" {{ $hasItem ? '' : 'disabled' }} @if(!$hasItem) style="opacity: 0.5; cursor: not-allowed;" @endif>
                                        {{ !empty($item['is_sold_out']) ? '✅ Available' : '🚫 Mark Sold' }}
                                    </button>
                                    
                                    {{-- Copy Barcode Button --}}
                                    @if(!empty($item['barcode']))
                                        <button type="button" class="text-center bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold py-1 rounded transition copy-barcode" data-barcode="{{ $item['barcode'] }}">
                                            📋 Copy
                                        </button>
                                    @else
                                        <button type="button" class="text-center bg-slate-50 text-slate-400 text-xs font-semibold py-1 rounded cursor-not-allowed opacity-50" disabled>
                                            📋 Copy
                                        </button>
                                    @endif
                                </div>

                                {{-- Barcode Actions (Download/Print) --}}
                                @if(!empty($item['barcode']) && $hasItem)
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('barcode.download', ['solutionItem' => $itemId]) }}" class="text-center bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold py-1 rounded transition" title="Download Barcode">
                                            📥 Download
                                        </a>
                                        <a href="{{ route('barcode.print', ['solutionItem' => $itemId]) }}" class="text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold py-1 rounded transition" target="_blank" title="Print Barcode">
                                            🖨️ Print
                                        </a>
                                    </div>
                                @endif

                                {{-- Delete Button: Always visible, enabled if item exists --}}
                                <button type="button" class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-semibold py-2 rounded transition text-sm delete-menu-item" data-item-id="{{ $itemId }}" {{ $hasItem ? '' : 'disabled' }} @if(!$hasItem) style="opacity: 0.5; cursor: not-allowed;" @endif>
                                    🗑️ Delete
                                </button>
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
        
        // Update UI optimistically
        this.classList.toggle('bg-green-500');
        this.classList.toggle('bg-gray-300');
        this.querySelector('span').classList.toggle('translate-x-5');
        this.querySelector('span').classList.toggle('translate-x-1');
        this.dataset.display = isCurrentlyDisplayed ? 'false' : 'true';
        
        // Send update to server
        fetch(`/api/menu-items/${itemId}/toggle-display-on-website`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(async res => {
            if (!res.ok) throw new Error('Failed to update website visibility');
            const data = await res.json();
            const isDisplayed = !!data.display_on_website;
            this.dataset.display = isDisplayed ? 'true' : 'false';
            const card = this.closest('.product-card');
            if (card) {
                card.dataset.website = isDisplayed ? 'visible' : 'hidden';
                const badge = card.querySelector('.website-badge');
                if (badge) {
                    badge.textContent = isDisplayed ? '✓ Web' : '✗ Web';
                    badge.classList.toggle('bg-blue-100', isDisplayed);
                    badge.classList.toggle('text-blue-800', isDisplayed);
                    badge.classList.toggle('bg-gray-200', !isDisplayed);
                    badge.classList.toggle('text-gray-700', !isDisplayed);
                }
                if (typeof filterProducts === 'function') filterProducts();
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

document.querySelectorAll('.toggle-sold-out').forEach(button => {
    button.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        if (!itemId) return;

        fetch(`/api/menu-items/${itemId}/toggle-sold-out`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(async res => {
            if (!res.ok) throw new Error('Failed to toggle sold-out status');
            const data = await res.json();
            const card = this.closest('.product-card');
            if (!card) return;

            const isSoldOut = !!data.is_sold_out;
            card.dataset.soldOut = isSoldOut ? 'true' : 'false';
            card.dataset.stock = isSoldOut ? 'sold-out' : 'in-stock';

            this.textContent = isSoldOut ? '✅ Mark Available' : '🚫 Mark Sold Out';

            const badge = card.querySelector('.stock-badge');
            if (badge) {
                badge.textContent = isSoldOut ? '✗ Sold Out' : `✓ ${data.stock ?? 0}`;
                badge.classList.toggle('bg-red-100', isSoldOut);
                badge.classList.toggle('text-red-800', isSoldOut);
                badge.classList.toggle('bg-green-100', !isSoldOut);
                badge.classList.toggle('text-green-800', !isSoldOut);
            }
            if (typeof filterProducts === 'function') filterProducts();
        })
        .catch(error => {
            console.error('Error toggling sold-out status:', error);
            alert('Could not update sold-out status. Please try again.');
        });
    });
});

document.querySelectorAll('.copy-barcode').forEach(button => {
    button.addEventListener('click', async function() {
        const barcode = this.dataset.barcode || '';
        if (!barcode) return;

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(barcode);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = barcode;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
            alert('Barcode copied.');
        } catch (error) {
            console.error('Error copying barcode:', error);
            alert('Could not copy barcode.');
        }
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
