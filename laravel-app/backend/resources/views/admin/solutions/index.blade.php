@extends('admin.layout')



@section('content')
<div class="container mx-auto py-8 px-4">

    
        
            
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
                                <span><strong>Status:</strong> <span class="status-badge {{ $solution->active ? 'status-active' : 'status-inactive' }}">{{ $solution->active ? 'Active' : 'Inactive' }}</span></span>
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
</div>
@endsection
