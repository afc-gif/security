@extends('layout')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="/" class="btn btn-outline-secondary">← Back to Shop</a>
        </div>
    </div>

    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            @if($product->image)
                <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 500px; object-fit: cover;">
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 500px;">
                    <i class="fas fa-image" style="font-size: 100px; color: #ccc;"></i>
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="mb-2">{{ $product->name }}</h1>
            
            @if($product->solution)
                <p class="text-muted mb-3">
                    <small>Category: <strong>{{ $product->solution->name }}</strong></small>
                </p>
            @endif

            <div class="mb-4">
                <h3 class="text-primary">₦{{ number_format($product->price ?? 0, 2) }}</h3>
            </div>

            <div class="mb-4">
                <p class="lead">{{ $product->description }}</p>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                @if(($product->stock ?? 0) > 0)
                    <p class="text-success">
                        <i class="fas fa-check-circle"></i> In Stock ({{ $product->stock }} available)
                    </p>
                @else
                    <p class="text-danger">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </p>
                @endif
            </div>

            <!-- Add to Cart Form -->
            @auth
                @if(($product->stock ?? 0) > 0)
                    <form action="{{ route('shop.addToCart', $product) }}" method="POST">
                        @csrf
                        <div class="input-group mb-3" style="max-width: 200px;">
                            <label class="input-group-text">Quantity:</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="{{ $product->stock ?? 1 }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                @else
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-ban"></i> Out of Stock
                    </button>
                @endif
            @else
                <p class="text-muted mb-3">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login to Purchase</a>
                </p>
            @endauth
        </div>
    </div>

</div>

<style>
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
@endsection
