<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ARTSCI - POS System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('extra-css')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="nav-logo" aria-label="ARTSCI home">
                <img src="{{ asset('images/logo.png') }}" alt="ARTSCI logo" class="nav-logo-img">
                <div class="brand-text">
                    <span class="brand-name">ARTSCI</span>
                    <span class="brand-tagline">Security POS Suite</span>
                </div>
            </a>
            <button class="nav-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li><a href="/">Shop</a></li>
                @if(auth()->check())
                    <li><a href="{{ route('cart') }}"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="{{ route('orders.index') }}">My Orders</a></li>
                    @if(auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.dashboard') }}" class="admin-link">Admin</a></li>
                    @endif
                    <li class="dropdown">
                        <a href="#">{{ auth()->user()->name }}</a>
                        <div class="dropdown-menu">
                            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="cta-nav">Login</a></li>
                    <li><a href="{{ route('register') }}" class="cta-nav">Register</a></li>
                @endif
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            <button onclick="this.parentElement.style.display='none';" class="close-btn">&times;</button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-error">
            {{ session('error') }}
            <button onclick="this.parentElement.style.display='none';" class="close-btn">&times;</button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 ARTSCI. All rights reserved.</p>
            <div class="footer-links">
                <a href="/">Home</a>
                <a href="/">Shop</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('extra-js')
</body>
</html>
