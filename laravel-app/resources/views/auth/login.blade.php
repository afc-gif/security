<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ARTSCI</title>
    <link rel="icon" type="image/webp" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
            <div>
                <div class="text-center mb-6">
                    <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI Logo" class="h-16 w-auto mx-auto">
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    ARTSCI Login
                </h2>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4">
                    <div class="text-sm font-medium text-red-800">
                        <strong>Login Failed!</strong>
                        @foreach ($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <div class="text-sm font-medium text-green-800">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                @csrf

                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <input
                            id="email-address"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Email address"
                        >
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Password"
                        >
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Sign in
                    </button>
                </div>
            </form>

            <div class="text-center">
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Don't have an account? Register
                </a>
            </div>
        </div>
    </div>
</body>
</html>
