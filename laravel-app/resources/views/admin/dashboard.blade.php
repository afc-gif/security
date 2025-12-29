@extends('layout')

@section('title', 'Admin Dashboard - ARTSCI')

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'dashboard'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-secondary">Logout</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(3, 169, 244, 0.1); color: var(--primary-blue);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Products</p>
                    <p class="stat-value">{{ $totalProducts }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1); color: #4CAF50;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Orders</p>
                    <p class="stat-value">{{ $totalOrders }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--primary-yellow);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Revenue</p>
                    <p class="stat-value">${{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(233, 30, 99, 0.1); color: #E91E63;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Recent Orders</h2>
            @if($recentOrders->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->user->name }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</div>
@endsection
