@extends('admin.layout')

@section('content')
<div class="container mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome to your admin dashboard</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalProducts }}</p>
                </div>
                <div class="text-blue-600 text-4xl">■</div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totalOrders }}</p>
                </div>
                <div class="text-green-600 text-4xl">■</div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">₦{{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="text-yellow-600 text-4xl">■</div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Users</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $totalUsers }}</p>
                </div>
                <div class="text-orange-600 text-4xl">■</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Orders</h2>
        @if($recentOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $order->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₦{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($order->status === 'completed') bg-green-100 text-green-800
                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 py-8">No recent orders yet</p>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.products.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition text-center hover:bg-blue-50">
            <div class="text-4xl mb-3">■</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Manage Products</h3>
            <p class="text-gray-600 text-sm">Create, edit, and manage your product inventory</p>
        </a>

        <a href="{{ route('admin.solutions.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition text-center hover:bg-green-50">
            <div class="text-4xl mb-3">■</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Manage Solutions</h3>
            <p class="text-gray-600 text-sm">Organize products into bundles and categories</p>
        </a>

        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition text-center hover:bg-orange-50">
            <div class="text-4xl mb-3">■</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Manage Users</h3>
            <p class="text-gray-600 text-sm">Approve users and manage team members</p>
        </a>
    </div>
</div>
@endsection
