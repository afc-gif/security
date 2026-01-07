@extends('layout')

@section('title', 'Shop - ARTSCI')

@section('content')
<section class="products-section">
    <div class="container">
        <h1 class="page-title">Our Products</h1>
        <p class="page-subtitle">Browse our selection of quality products</p>

        @if($products->count() > 0)
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        @if($product->image)
                            <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" class="product-image">
                        @else
                            <div class="product-image-placeholder">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif

                        <div class="product-info">
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <p class="product-category">{{ $product->solution->name ?? 'Uncategorized' }}</p>
                            <p class="product-description">{{ Str::limit($product->description, 50) }}</p>

                            <div class="product-footer">
                                <span class="product-price">₦{{ number_format($product->price ?? 0, 2) }}</span>
                                @if(($product->stock ?? 0) > 0)
                                    <span class="stock-badge in-stock">In Stock</span>
                                @else
                                    <span class="stock-badge out-of-stock">Out of Stock</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('shop.addToCart', $product) }}" class="add-to-cart-form">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                @if(($product->stock ?? 0) > 0)
                                    <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                @else
                                    <button type="button" class="btn btn-disabled btn-sm" disabled>Out of Stock</button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>No Products Available</h2>
                <p>Check back soon for new products!</p>
            </div>
        @endif
    </div>
</section>
@endsection

@section('extra-css')
<style>
    .products-section {
        padding: 60px 20px;
        background: #F0F4F9;
        min-height: calc(100vh - 120px);
    }

    .container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .page-title {
        font-size: 36px;
        font-weight: 700;
        color: #0A1428;
        margin-bottom: 12px;
    }

    .page-subtitle {
        font-size: 16px;
        color: #8A95A8;
        margin-bottom: 40px;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
        transition: all 0.3s ease;
    }

    .product-card:hover {
        box-shadow: 0 8px 24px rgba(10, 20, 40, 0.12);
        transform: translateY(-4px);
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f5f5f5;
    }

    .product-image-placeholder {
        width: 100%;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #E0E6EF;
        color: #8A95A8;
        font-size: 48px;
    }

    .product-info {
        padding: 20px;
    }

    .product-name {
        font-size: 18px;
        font-weight: 600;
        color: #0A1428;
        margin-bottom: 4px;
    }

    .product-category {
        font-size: 12px;
        color: #8A95A8;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .product-description {
        font-size: 14px;
        color: #6B7784;
        margin-bottom: 16px;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .product-price {
        font-size: 24px;
        font-weight: 700;
        color: #03A9F4;
    }

    .stock-badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .stock-badge.in-stock {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .stock-badge.out-of-stock {
        background: #FFEBEE;
        color: #C62828;
    }

    .add-to-cart-form {
        width: 100%;
    }

    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-primary {
        background: #03A9F4;
        color: white;
    }

    .btn-primary:hover {
        background: #0285C2;
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 13px;
    }

    .btn-disabled {
        background: #E0E6EF;
        color: #8A95A8;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #8A95A8;
    }

    .empty-state i {
        font-size: 64px;
        display: block;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h2 {
        font-size: 24px;
        color: #0A1428;
        margin-bottom: 10px;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 40px;
    }

    .pagination-container nav {
        display: flex;
        gap: 10px;
    }

    .pagination-container a, .pagination-container span {
        padding: 8px 12px;
        border: 1px solid #E0E6EF;
        border-radius: 6px;
        color: #03A9F4;
        text-decoration: none;
    }

    .pagination-container .active span {
        background: #03A9F4;
        color: white;
    }
</style>
@endsection
