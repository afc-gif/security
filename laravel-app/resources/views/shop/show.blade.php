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
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 500px; object-fit: cover;">
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 500px;">
                    <i class="fas fa-image" style="font-size: 100px; color: #ccc;"></i>
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="mb-2">{{ $product->name }}</h1>
            
            @if($product->category)
                <p class="text-muted mb-3">
                    <small>Category: <strong>{{ $product->category }}</strong></small>
                </p>
            @endif

            <div class="mb-4">
                <h3 class="text-primary">${{ number_format($product->price, 2) }}</h3>
            </div>

            <div class="mb-4">
                <p class="lead">{{ $product->description }}</p>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                @if($product->stock > 0)
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
                @if($product->stock > 0)
                    <form action="{{ route('cart.add', $product->id) }}" method="POST">
                        @csrf
                        <div class="input-group mb-3" style="max-width: 200px;">
                            <label class="input-group-text">Quantity:</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}" required>
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

    <!-- Related Products (Optional) -->
    @if($product->category)
        <hr class="my-5">
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">More from {{ $product->category }}</h3>
            </div>
            @foreach(\App\Models\Product::where('category', $product->category)->where('id', '!=', $product->id)->limit(4)->get() as $related)
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        @if($related->image)
                            <img src="{{ $related->image }}" class="card-img-top" alt="{{ $related->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image" style="font-size: 50px; color: #ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $related->name }}</h5>
                            <p class="card-text text-primary">${{ number_format($related->price, 2) }}</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('products.show', $related->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
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
