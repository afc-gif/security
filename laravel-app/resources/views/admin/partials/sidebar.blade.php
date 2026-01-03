@php($active = $active ?? '')

<aside class="admin-sidebar">
    <div class="admin-brand">
        <img src="{{ asset('images/logo.png') }}" alt="ARTSCI logo">
        <div class="brand-text">
            <span class="brand-name">ARTSCI</span>
            <span class="brand-tagline">Admin Console</span>
        </div>
    </div>
    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="{{ route('admin.solutions.index') }}" class="nav-item {{ $active === 'solutions' ? 'active' : '' }}">
            <i class="fas fa-cube"></i> Categories
        </a>
        <a href="{{ route('admin.products.index') }}" class="nav-item {{ $active === 'products' ? 'active' : '' }}">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="{{ route('admin.orders.index') }}" class="nav-item {{ $active === 'orders' ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ $active === 'users' ? 'active' : '' }}">
            <i class="fas fa-users"></i> Users
        </a>
    </nav>
</aside>
<div class="admin-sidebar-backdrop"></div>
