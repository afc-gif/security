@extends('admin.layout')

@section('title', 'Order #' . $order->id . ' - ARTSCI Admin')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }} Details</h1>
            <p class="text-gray-600">Review order information and items.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex rounded bg-gray-200 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-300">Back to Orders</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Order Information</h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-gray-600">Order ID</span>
                    <span class="text-gray-900">#{{ $order->id }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-gray-600">Customer</span>
                    <span class="text-right text-gray-900">{{ $order->user->name ?? 'Guest' }}@if($order->user) ({{ $order->user->email }})@endif</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-gray-600">Date</span>
                    <span class="text-gray-900">{{ $order->created_at->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-gray-600">Status</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-gray-600">Total Amount</span>
                    <span class="font-bold text-gray-900">₦{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="mt-6 grid gap-3">
                @csrf
                @method('PATCH')

                <label for="status" class="text-sm font-semibold text-gray-600">Update Status</label>
                <select id="status" name="status" required class="rounded border border-gray-300 px-3 py-2">
                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Update Status</button>
            </form>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Order Items</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Product</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Price</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Quantity</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            @php($itemName = $item->product?->name ?? $item->solutionItem?->name ?? $item->name ?? 'Item')
                            @php($unitPrice = $item->unit_price ?? $item->price ?? 0)
                            @php($lineTotal = $item->total ?? ($unitPrice * $item->quantity))
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900">{{ $itemName }}</td>
                                <td class="px-4 py-3 text-gray-700">₦{{ number_format($unitPrice, 2) }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-900">₦{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
