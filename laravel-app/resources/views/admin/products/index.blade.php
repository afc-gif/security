@extends('admin.layout')

@section('content')
<div class="container mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Products Management</h1>
            <p class="text-gray-600 mt-2">Manage products and generate barcodes for POS system</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            + Create Product
        </a>
    </div>

    @php
        $flattened = [];
        $rowId = 1;
        foreach ($solutionProducts as $category) {
            foreach ($category['items'] as $item) {
                $flattened[] = [
                    'row_id' => $rowId++,
                    'category' => $category['title'] ?? 'Uncategorized',
                    'solution_id' => $category['id'] ?? null,
                    'item' => $item,
                ];
            }
        }
    @endphp

    @if(count($flattened) > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barcode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($flattened as $row)
                        @php($item = $row['item'])
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                @else
                                    <span class="text-gray-400 text-sm">No image</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $item['name'] }}</div>
                                <div class="text-sm text-gray-600">{{ substr($item['description'] ?? '', 0, 50) }}...</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($item['barcode']))
                                    <code class="bg-gray-100 px-3 py-1 rounded text-sm font-mono">{{ $item['barcode'] }}</code>
                                    @if(!empty($item['id']) && !empty($item['solution_id']))
                                        <div class="mt-2 space-x-2">
                                            <a href="{{ route('barcode.download', ['solutionItem' => $item['id']]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Download</a>
                                            <a href="{{ route('barcode.print', ['solutionItem' => $item['id']]) }}" class="text-green-600 hover:text-green-800 text-sm font-semibold" target="_blank">Print</a>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $row['category'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $item['price'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php($stock = $item['stock'] ?? 0)
                                <span class="px-3 py-1 rounded text-sm font-semibold @if($stock > 0) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                    {{ $stock > 0 ? $stock . ' in stock' : 'Sold Out' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                @if(!empty($item['id']) && !empty($item['solution_id']))
                                    <a href="{{ route('admin.solutions.items.edit', [$row['solution_id'], $item['id']]) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                                    <form action="{{ route('admin.solutions.items.destroy', [$row['solution_id'], $item['id']]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold" onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                @else
                                    <span class="text-gray-400">View only</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <p class="text-blue-700 font-semibold">No products yet</p>
            <p class="text-blue-600 mt-2">Create your first product to get started. Products will appear in the POS system for sales.</p>
        </div>
    @endif
</div>
@endsection
