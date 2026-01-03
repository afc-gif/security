@extends('layout')

@section('title', 'Solutions/Categories - Admin - ARTSCI')

@section('extra-css')
<style>
    .solution-card {
        background: white;
        border: 1px solid #E0E6EF;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .solution-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        border-color: #03A9F4;
    }

    .solution-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .solution-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .solution-title h3 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .solution-icon {
        font-size: 28px;
        line-height: 1;
    }

    .solution-description {
        color: #6B7280;
        font-size: 14px;
        margin: 8px 0 0 0;
    }

    .solution-meta {
        display: flex;
        gap: 24px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #E5E7EB;
        font-size: 13px;
        color: #6B7280;
    }

    .solution-actions {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        padding: 8px 12px;
        font-size: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
    }

    .btn-view {
        background: #E0F2FE;
        color: #0369A1;
    }

    .btn-view:hover {
        background: #0369A1;
        color: white;
    }

    .btn-edit {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .btn-edit:hover {
        background: #1D4ED8;
        color: white;
    }

    .btn-delete {
        background: #FEE2E2;
        color: #991B1B;
    }

    .btn-delete:hover {
        background: #991B1B;
        color: white;
    }

    .empty-state {
        background: #FFFBEB;
        border: 1px solid #FCD34D;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        color: #92400E;
    }

    .empty-state h2 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 18px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'solutions'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Categories Management</h1>
            </div>
            <a href="{{ route('admin.solutions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; border-radius: 8px; color: #065F46; padding: 12px 16px; margin-bottom: 24px;">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                {{ session('success') }}
            </div>
        @endif

        <div style="max-width: 100%;">
            @forelse ($solutions as $solution)
                <div class="solution-card">
                    <div class="solution-header">
                        <div style="flex: 1;">
                            <div class="solution-title">
                                <span class="solution-icon">{{ $solution->icon }}</span>
                                <h3>{{ $solution->name }}</h3>
                            </div>
                            @if ($solution->description)
                                <p class="solution-description">{{ $solution->description }}</p>
                            @endif
                            <div class="solution-meta">
                                <span><strong>Products:</strong> {{ $solution->items->count() }}</span>
                                <span><strong>Status:</strong> <span style="color: {{ $solution->active ? '#10B981' : '#EF4444' }};">{{ $solution->active ? 'Active' : 'Inactive' }}</span></span>
                                <span><strong>Order:</strong> {{ $solution->sort_order ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="solution-actions">
                            <a href="{{ route('admin.solutions.show', $solution) }}" class="btn-icon btn-view" title="View products in this category">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('admin.solutions.edit', $solution) }}" class="btn-icon btn-edit" title="Edit category">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.solutions.destroy', $solution) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-delete" title="Delete category">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h2><i class="fas fa-inbox"></i> No Categories Found</h2>
                    <p>Get started by creating your first product category.</p>
                    <a href="{{ route('admin.solutions.create') }}" class="btn btn-primary" style="display: inline-block; margin-top: 12px;">
                        <i class="fas fa-plus"></i> Create Category
                    </a>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
