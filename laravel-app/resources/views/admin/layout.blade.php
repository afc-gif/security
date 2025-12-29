<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ARTSCI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white p-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Panel</h1>
            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <a href="{{ route('admin.categories.index') }}" class="hover:underline">Categories</a>
                <a href="{{ route('admin.menu-items.index') }}" class="hover:underline">Menu Items</a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:underline">Logout</button>
                </form>
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
