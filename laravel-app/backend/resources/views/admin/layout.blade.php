<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ARTSCI</title>
    <link rel="icon" type="image/webp" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-gradient-to-r from-blue-700 to-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI" class="h-10 w-auto">
                <h1 class="text-2xl font-bold">ARTSCI Admin</h1>
            </div>
            <div class="flex gap-6 items-center">
                <a href="{{ route('admin.dashboard') }}" class="hover:bg-blue-500 px-3 py-2 rounded transition">Dashboard</a>
                <a href="{{ route('admin.products.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded transition">Products</a>
                <a href="{{ route('admin.solutions.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded transition">Solutions</a>
                <a href="{{ route('admin.users.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded transition">Users</a>
                <a href="{{ route('pos.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded transition" title="Go to POS System">🛒 POS</a>
                
                <!-- User Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-2 hover:bg-blue-500 px-3 py-2 rounded transition">
                        <span class="text-sm">{{ Auth::user()->name ?? 'User' }}</span>
                        <span>▼</span>
                    </button>
                    <div class="absolute right-0 mt-0 w-48 bg-white text-gray-800 rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="font-semibold text-sm">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-600">{{ Auth::user()->email ?? 'user@example.com' }}</p>
                            <span class="inline-block mt-1 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">{{ Auth::user()->role ?? 'user' }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 font-semibold transition">
                                🚪 Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Live Polling JavaScript -->
    <script>
        // Poll for category/menu updates every 3 seconds
        setInterval(function() {
            axios.get('/api/categories')
                .then(response => {
                    // Update categories display
                    console.log('Categories updated:', response.data);
                    // You can emit a custom event here for real-time updates
                    window.dispatchEvent(new CustomEvent('categoriesUpdated', { detail: response.data }));
                })
                .catch(error => console.error('Error fetching categories:', error));
        }, 3000);

        // Listen for category updates
        window.addEventListener('categoriesUpdated', (event) => {
            const categories = event.detail;
            // Update UI with new categories
            console.log('UI Updated with new categories');
        });
    </script>
</body>
</html>
