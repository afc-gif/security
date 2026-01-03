@extends('layout')

@section('title', 'Edit Category - Admin - ARTSCI')

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'solutions'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Edit Category</h1>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.solutions.update', $solution) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $solution->name) }}" required>
                    @error('name')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="icon">Icon (Emoji) *</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon', $solution->icon) }}" maxlength="10" required>
                    <span class="helper-text">Use an emoji to represent this category</span>
                    @error('icon')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description">{{ old('description', $solution->description) }}</textarea>
                    @error('description')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $solution->sort_order) }}" min="0">
                    <span class="helper-text">Lower numbers appear first</span>
                    @error('sort_order')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="{{ route('admin.solutions.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
