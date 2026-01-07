@extends('layout')

@section('title', 'Order Details - ARTSCI')

@section('content')
<section class="order-details-section">
    <div class="container">
        <h1 class="page-title">Order #{{ $order->id }}</h1>

        <div class="order-info-grid">
            <div class="info-card">
                <h3>Order Information</h3>
                <div class="info-item">
                    <span class="label">Order Date:</span>
                    <span class="value">{{ $order->created_at->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <div class="info-card">
                <h3>Order Summary</h3>
                <div class="info-item">
                    <span class="label">Total Items:</span>
                    <span class="value">{{ $order->items->count() }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Total Amount:</span>
                    <span class="value amount">₦{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="order-items-card">
            <h2>Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->solutionItem->name ?? $item->name }}</td>
                            <td>₦{{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₦{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="action-buttons">
            <a href="{{ route('orders.index') }}" class="btn btn-primary">Back to Orders</a>
            <a href="/" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</section>
@endsection

@section('extra-css')
<style>
    .order-details-section {
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

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .info-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
    }

    .info-card h3 {
        margin-bottom: 20px;
        color: #0A1428;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #E0E6EF;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .label {
        font-weight: 600;
        color: #0A1428;
    }

    .value {
        color: #6B7784;
    }

    .value.amount {
        color: #03A9F4;
        font-weight: 700;
        font-size: 18px;
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

    .order-items-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(10, 20, 40, 0.08);
        margin-bottom: 40px;
    }

    .order-items-card h2 {
        margin-bottom: 20px;
        color: #0A1428;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead {
        background: #F0F4F9;
    }

    .items-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #0A1428;
        border-bottom: 1px solid #E0E6EF;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #E0E6EF;
        color: #6B7784;
    }

    .items-table tr:hover {
        background: #F9FAFB;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
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
</style>
@endsection
