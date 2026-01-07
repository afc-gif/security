@extends('admin.layout')


@section('content')
<div class="container mx-auto py-8 px-4">

    
        
            
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Order #{{ $order->id }} Details</h1>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
        </div>

        <div class="order-details-container">
            <div class="order-info-card">
                <h2>Order Information</h2>
                <div class="info-row">
                    <span class="label">Order ID:</span>
                    <span class="value">#{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Customer:</span>
                    <span class="value">{{ $order->user->name }} ({{ $order->user->email }})</span>
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ $order->created_at->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total Amount:</span>
                    <span class="value price">${{ number_format($order->total_amount, 2) }}</span>
                </div>

                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="status-form">
                    @csrf
                    @method('PATCH')
                    
                        <label for="status">Update Status:</label>
                        <select id="status" name="status" required>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                    </div>
                </form>
            </div>

            <div class="order-items-card">
                <h2>Order Items</h2>
                <table class="data-table">
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
                                <td>{{ $item->product->name }}</td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>
@endsection
