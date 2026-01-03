@extends('layout')

@section('title', 'Products - Admin - ARTSCI')

@section('extra-css')
<style>
    .solution-sync-card {
        background: white;
        border: 1px solid #E0E6EF;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
    }
    .solution-category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }
    .solution-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #F0F4FF;
        color: #0366d6;
        font-weight: 600;
        font-size: 12px;
    }
    .solution-item-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #EEF1F7;
    }
    .solution-item-row:first-child {
        border-top: none;
    }
    .solution-item-name {
        font-weight: 700;
        color: #111827;
    }
    .solution-item-desc {
        color: #4B5563;
        font-size: 14px;
        margin: 6px 0;
    }
    .solution-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .solution-specs span {
        background: #F3F4F6;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 12px;
        color: #374151;
    }
    .solution-price {
        min-width: 140px;
        text-align: right;
        font-weight: 700;
        color: #0F766E;
    }
    .solution-meta {
        color: #6B7280;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'products'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Products Management</h1>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>

        <div class="solution-sync-card">
            <div class="solution-category-header">
                <div>
                    <h2 style="margin: 0 0 6px 0;">Solutions.html Snapshot</h2>
                    <p class="solution-meta">Data pulled directly from <code>public/solutions.html</code> so the admin view matches the live solutions & product cards.</p>
                </div>
                <span class="solution-chip">{{ count($solutionProducts ?? []) }} categories</span>
            </div>

            @forelse($solutionProducts as $category)
                <div style="border: 1px solid #EEF1F7; border-radius: 10px; padding: 12px; margin-bottom: 12px;">
                    <div class="solution-category-header">
                        <div>
                            @if($category['id'])
                                <span class="solution-chip">#{{ $category['id'] }}</span>
                            @endif
                            <div style="font-weight: 700; font-size: 18px; margin: 6px 0;">{{ $category['title'] }}</div>
                            @if(!empty($category['description']))
                                <p class="solution-meta" style="margin: 0;">{{ $category['description'] }}</p>
                            @endif
                        </div>
                        <span class="badge">{{ count($category['items']) }} items</span>
                    </div>

                    <div>
                        @foreach($category['items'] as $item)
                            <div class="solution-item-row">
                                <div style="flex: 1;">
                                    <div class="solution-item-name">{{ $item['name'] }}</div>
                                    @if(!empty($item['description']))
                                        <div class="solution-item-desc">{{ $item['description'] }}</div>
                                    @endif
                                    @if(!empty($item['specs']))
                                        <div class="solution-specs">
                                            @foreach($item['specs'] as $spec)
                                                <span>{{ $spec }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="solution-price">
                                    {{ $item['price'] ?? 'N/A' }}
                                    @if(!empty($item['image']))
                                        <div class="solution-meta" style="margin-top: 6px; word-break: break-all;">img: {{ $item['image'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>No products found in solutions.html. Confirm the file exists in <code>public/solutions.html</code>.</p>
                </div>
            @endforelse
        </div>

        @if($products->count() > 0)
            <div class="products-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>#{{ $product->id }}</td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>{{ $product->category ?? 'N/A' }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>
                                    <span class="stock-badge {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-edit">Edit</a>
                                    <form action="{{ route('admin.products.delete', $product) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        @else
            <div class="empty-state">
                <p>No products found. <a href="{{ route('admin.products.create') }}">Create one</a></p>
            </div>
        @endif
    </main>
</div>
@endsection
