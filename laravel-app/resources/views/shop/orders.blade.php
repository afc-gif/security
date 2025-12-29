@extends('layout')

@section('title', 'My Orders - ARTSCI')

@section('content')
<section class="orders-section">
    <div class="container">
        <h1 class="page-title">My Orders</h1>

        @if($orders->count() > 0)
            <div class="orders-list">
                @foreach($orders as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3>Order #{{ $order->id }}</h3>
                                <p class="order-date">{{ $order->created_at->format('M d, Y - h:i A') }}</p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-{{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Items</span>
                                <span class="detail-value">{{ $order->items->count() }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total</span>
                                <span class="detail-value">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary btn-sm">View Details</a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $orders->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>No Orders Yet</h2>
                <p>Start shopping to place your first order!</p>
                <a href="/" class="btn btn-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</section>
@endsection

@section('extra-css')
<style>
    .orders-section {
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
        margin-bottom: 40px;
    }

    .orders-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    @media (max-width: 768px) {
        .orders-list {
            grid-template-columns: 1fr;
        }
    }

    .order-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 8px 24px rgba(10, 20, 40, 0.12);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #E0E6EF;
    }

    .order-header h3 {
        color: #0A1428;
        margin-bottom: 4px;
    }

    .order-date {
        font-size: 12px;
        color: #8A95A8;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.status-completed {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.status-pending {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.status-cancelled {
        background: #FFEBEE;
        color: #C62828;
    }

    .order-details {
        display: flex;
        gap: 30px;
        margin-bottom: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 12px;
        color: #8A95A8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 18px;
        font-weight: 700;
        color: #0A1428;
    }

    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary {
        background: #E0E6EF;
        color: #0A1428;
    }

    .btn-secondary:hover {
        background: #D0D8E0;
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 13px;
    }

    .btn-primary {
        background: #03A9F4;
        color: white;
    }

    .btn-primary:hover {
        background: #0285C2;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
    }

    .empty-state i {
        font-size: 64px;
        color: #E0E6EF;
        display: block;
        margin-bottom: 20px;
    }

    .empty-state h2 {
        color: #0A1428;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #8A95A8;
        margin-bottom: 30px;
    }
</style>
@endsection
