@extends('admin.layout')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Installation</h1>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.installations.update', $installation) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title', $installation->title) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (optional)</label>
                        <input type="text" name="slug" value="{{ old('slug', $installation->slug) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <input type="text" name="category" value="{{ old('category', $installation->category) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                        <input type="text" name="city" value="{{ old('city', $installation->city) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Type</label>
                        <input type="text" name="client_type" value="{{ old('client_type', $installation->client_type) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Completed Date</label>
                        <input type="date" name="completed_at" value="{{ old('completed_at', optional($installation->completed_at)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $installation->sort_order) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Summary *</label>
                    <textarea name="summary" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('summary', $installation->summary) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Outcome</label>
                    <textarea name="outcome" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('outcome', $installation->outcome) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cover Image</label>
                        @if($installation->cover_image)
                            <img src="{{ \App\Support\ImageUrl::url($installation->cover_image) }}" alt="{{ $installation->title }}" class="h-28 w-44 object-cover rounded border border-gray-200 mb-2">
                        @endif
                        <input type="file" name="cover_image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Add Gallery Images</label>
                        <input type="file" name="gallery_images[]" multiple accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>

                @if(!empty($installation->gallery_images))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Existing Gallery Images</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($installation->gallery_images as $index => $galleryImage)
                                <label class="border border-gray-200 rounded-lg p-2 bg-white">
                                    <img src="{{ \App\Support\ImageUrl::url($galleryImage) }}" alt="Gallery image {{ $index + 1 }}" class="h-24 w-full object-cover rounded mb-2">
                                    <span class="inline-flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox" name="remove_gallery_indexes[]" value="{{ $index }}">
                                        Remove image
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex gap-6">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $installation->is_featured))>
                        Featured
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $installation->is_public))>
                        Public on homepage
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Update Installation</button>
                    <a href="{{ route('admin.installations.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
