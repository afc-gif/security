@extends('layout')

@section('title', 'Add Product - Admin - ARTSCI')

@section('content')
<div class="admin-container">
    @include('admin.partials.sidebar', ['active' => 'products'])

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Add New Product</h1>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <input list="category-options" id="category" name="category" value="{{ old('category') }}" placeholder="Select or type a category">
                    <datalist id="category-options">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}"></option>
                        @endforeach
                    </datalist>
                    <span class="helper-text">Existing admin categories are suggested, but you can type a new one.</span>
                    @error('category')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required>
                    @error('price')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" required>
                    @error('stock')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description">{{ old('description') }}</textarea>
                    @error('description')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    @error('image')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
