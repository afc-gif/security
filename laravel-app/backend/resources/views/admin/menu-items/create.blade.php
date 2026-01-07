@extends('admin.layout')


@section('content')
<div class="container mx-auto py-8 px-4">

    
        
            
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Add New Product</h1>
            </div>
        </div>

        
            <form method="POST" action="{{ route('menu-items.store') }}" enctype="multipart/form-data">
                @csrf

                
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="description">Description</label>
                    <textarea id="description" name="description">{{ old('description') }}</textarea>
                    @error('description')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required>
                    @error('price')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    @error('image')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                    @error('sort_order')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label>
                        <input type="checkbox" name="available" value="1" {{ old('available') ? 'checked' : '' }}>
                        Available (Show on website)
                    </label>
                    @error('available')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <!-- Actions -->
                    <button type="submit" class="btn btn-primary">Create Product</button>
                    <a href="{{ route('menu-items.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
</div>
@endsection
