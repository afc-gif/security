@extends('layout')

@section('title', 'Products - Admin - ARTSCI')

@section('extra-css')
<style>
    .solution-sync-card {
        background: white;
        border: 1px solid #E0E6EF;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
    }
    .solution-category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }
    .solution-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #F0F4FF;
        color: #0366d6;
        font-weight: 600;
        font-size: 12px;
    }
    .solution-item-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #EEF1F7;
    }
    .solution-item-row:first-child {
        border-top: none;
    }
    .solution-item-name {
        font-weight: 700;
        color: #111827;
    }
    .solution-item-desc {
        color: #4B5563;
        font-size: 14px;
        margin: 6px 0;
    }
    .solution-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .solution-specs span {
        background: #F3F4F6;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 12px;
        color: #374151;
    }
    .solution-price {
        min-width: 140px;
        text-align: right;
        font-weight: 700;
        color: #0F766E;
    }
    .solution-meta {
        color: #6B7280;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'products'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Products Management</h1>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
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
                        'item' => $item,
                    ];
                }
            }
        @endphp

        <div class="solution-category-header">
            <div>
                <h2 style="margin: 0 0 6px 0;">Solutions.html Snapshot (Marketing)</h2>
                <p class="solution-meta">Source: <code>public/solutions.html</code>. This table replaces the old database list in admin.</p>
            </div>
            <span class="solution-chip">{{ count($solutionProducts ?? []) }} categories</span>
        </div>

        @if(count($flattened) > 0)
            <div class="products-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flattened as $row)
                            @php($item = $row['item'])
                            <tr>
                                <td>
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #E5E7EB;">
                                    @else
                                        <span class="solution-meta">No image</span>
                                    @endif
                                </td>
                                <td>#{{ $row['row_id'] }}</td>
                                <td><strong>{{ $item['name'] }}</strong></td>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $item['price'] ?? 'N/A' }}</td>
                                <td><span class="stock-badge out-of-stock">N/A</span></td>
                                <td><span class="solution-meta">Snapshot only</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <p>No products found in <code>public/solutions.html</code>. Add cards there to display here.</p>
            </div>
        @endif
    </main>
</div>
@endsection
