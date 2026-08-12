<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden | ARTSCI Security</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 text-gray-800">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
            !
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Forbidden (403)</h1>
        <p class="text-gray-600 mb-6 text-sm">
            {{ $exception->getMessage() ?: "You do not have permission to access this page. Please log in with an authorized account." }}
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="javascript:history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-sm transition">
                Go Back
            </a>
            <a href="/admin/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition">
                Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
