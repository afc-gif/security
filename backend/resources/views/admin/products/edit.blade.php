@extends('admin.layout')


@section('content')
<div class="container mx-auto py-8 px-4">

    
        
            
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Edit Product</h1>
            </div>
        </div>

        
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if($errors->has('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        {{ $errors->first('error') }}
                    </div>
                @endif

                
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select a Category --</option>
                        @forelse($categories as $cat)
                            <option value="{{ $cat->name }}" @if(old('category', $product->category) === $cat->name) selected @endif>
                                {{ $cat->icon ?? '' }} {{ $cat->name }}
                            </option>
                        @empty
                            <option value="" disabled>No categories available</option>
                        @endforelse
                    </select>
                    <span class="helper-text">Select from the 6 main solution categories</span>
                    @error('category')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" value="{{ old('price', $product->price) }}" required>
                    @error('price')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required>
                    @error('stock')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="description">Description</label>
                    <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
                    @error('description')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                
                    <label for="image">Product Image</label>
                    @if($product->image)
                        <div class="image-preview">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept="image/*">
                    @error('image')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <!-- Actions -->
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
</div>
@endsection
