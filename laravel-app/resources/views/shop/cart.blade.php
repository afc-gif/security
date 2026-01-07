@extends('layout')

@section('title', 'Shopping Cart - ARTSCI')

@section('content')
<section class="cart-section">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>

        @if(count($cart) > 0)
            <div class="cart-layout">
                <div class="cart-items">
                    @foreach($cart as $item)
                        <div class="cart-item">
                            <div class="item-image">
                                @if($item['image'])
                                    <img src="{{ Storage::disk('public')->url($item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <div class="placeholder"><i class="fas fa-box"></i></div>
                                @endif
                            </div>

                            <div class="item-details">
                                <h3>{{ $item['name'] }}</h3>
                                <p class="item-price">₦{{ number_format($item['price'], 2) }}</p>
                            </div>

                            <div class="item-quantity">
                                <span>Qty: {{ $item['quantity'] }}</span>
                            </div>

                            <div class="item-total">
                                <strong>₦{{ number_format($item['price'] * $item['quantity'], 2) }}</strong>
                            </div>

                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST" class="item-remove">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <strong>₦{{ number_format($total, 2) }}</strong>
                    </div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <strong>Free</strong>
                    </div>
                    <div class="summary-line total">
                        <span>Total</span>
                        <strong>₦{{ number_format($total, 2) }}</strong>
                    </div>

                    <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                        @csrf
                        <textarea name="notes" placeholder="Order notes (optional)" class="order-notes"></textarea>
                        <button type="submit" class="btn btn-primary btn-lg">Proceed to Checkout</button>
                    </form>

                    <a href="/" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        @else
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="/" class="btn btn-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</section>
@endsection

@section('extra-css')
<style>
    .cart-section {
        padding: 60px 20px;
        background: #F0F4F9;
        min-height: calc(100vh - 120px);
    }

    .page-title {
        font-size: 36px;
        font-weight: 700;
        color: #0A1428;
        margin-bottom: 40px;
    }

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 40px;
        align-items: start;
    }

    @media (max-width: 768px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }
    }

    .cart-items {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .cart-item {
        background: white;
        padding: 20px;
        border-radius: 12px;
        display: flex;
        gap: 20px;
        align-items: center;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
    }

    .item-image {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        background: #E0E6EF;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8A95A8;
        font-size: 32px;
    }

    .item-details {
        flex: 1;
    }

    .item-details h3 {
        color: #0A1428;
        margin-bottom: 8px;
    }

    .item-price {
        color: #03A9F4;
        font-weight: 600;
    }

    .item-quantity {
        min-width: 100px;
        text-align: center;
    }

    .item-total {
        min-width: 100px;
        text-align: right;
        font-size: 18px;
        color: #03A9F4;
    }

    .btn-remove {
        background: none;
        border: none;
        color: #d32f2f;
        cursor: pointer;
        font-size: 18px;
        padding: 8px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-remove:hover {
        background: #FFEBEE;
    }

    .item-remove {
        display: inline;
    }

    .cart-summary {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
        position: sticky;
        top: 80px;
    }

    .cart-summary h2 {
        font-size: 20px;
        margin-bottom: 20px;
        color: #0A1428;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #E0E6EF;
    }

    .summary-line.total {
        border-bottom: none;
        font-size: 18px;
        font-weight: 700;
        color: #03A9F4;
    }

    .order-notes {
        width: 100%;
        height: 80px;
        padding: 12px;
        border: 1px solid #E0E6EF;
        border-radius: 6px;
        font-family: inherit;
        resize: none;
        margin-bottom: 16px;
    }

    .checkout-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 16px;
    }

    .btn {
        padding: 12px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        display: block;
    }

    .btn-primary {
        background: #03A9F4;
        color: white;
    }

    .btn-primary:hover {
        background: #0285C2;
    }

    .btn-secondary {
        background: #E0E6EF;
        color: #0A1428;
    }

    .btn-secondary:hover {
        background: #D0D8E0;
    }

    .btn-lg {
        padding: 14px 20px;
        font-size: 15px;
    }

    .empty-cart {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
    }

    .empty-cart i {
        font-size: 64px;
        color: #E0E6EF;
        display: block;
        margin-bottom: 20px;
    }

    .empty-cart h2 {
        color: #0A1428;
        margin-bottom: 10px;
    }

    .empty-cart p {
        color: #8A95A8;
        margin-bottom: 30px;
    }
</style>
@endsection
