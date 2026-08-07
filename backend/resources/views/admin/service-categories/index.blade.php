@extends('admin.layout')

@section('title', 'Field Service Categories | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Field Service Categories</h1>
                <p class="text-sm text-gray-600 mt-1">Create job categories and reusable checklist templates for field work.</p>
            </div>
            <a href="{{ route('admin.job-requests.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                Create Job
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Add Category</h2>
            <form method="POST" action="{{ route('admin.service-categories.store') }}" class="grid grid-cols-1 lg:grid-cols-[1fr_1.5fr_auto] gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="CCTV Maintenance">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional description">
                </div>
                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 pb-2">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Active
                    </label>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Add</button>
                </div>
            </form>
        </div>

        <div class="space-y-5">
            @forelse($serviceCategories as $category)
                <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-4">
                        <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                                <input type="text" name="name" value="{{ $category->name }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input type="text" name="description" value="{{ $category->description }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="rounded border-gray-300 text-blue-600">
                                    Active
                                </label>
                                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Save Category</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" class="lg:text-right">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this category? Categories already used by jobs cannot be deleted.')">Delete</button>
                        </form>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Checklist Template</h3>
                            <div class="text-sm text-gray-600">{{ $category->checklistTemplates->count() }} item{{ $category->checklistTemplates->count() === 1 ? '' : 's' }}</div>
                        </div>

                        <form method="POST" action="{{ route('admin.service-categories.templates.store', $category) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            @csrf
                            <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Checklist item">
                            <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional instruction">
                            <select name="input_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="textarea">Long Text</option>
                                <option value="text">Short Text</option>
                                <option value="number">Number</option>
                                <option value="single_choice">Single Choice</option>
                                <option value="multi_choice">Multiple Choice</option>
                            </select>
                            <textarea name="options" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Options, one per line. Use for choice fields only."></textarea>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_required" value="1" checked class="rounded border-gray-300 text-blue-600">
                                    Required
                                </label>
                                <button type="submit" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-semibold">Add Item</button>
                            </div>
                        </form>

                        @if($category->checklistTemplates->isEmpty())
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                No default checklist yet. Jobs can still be created; admin, coordinator, or field staff can add job-specific checklist items.
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($category->checklistTemplates as $template)
                                    <form method="POST" action="{{ route('admin.service-categories.templates.update', $template) }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-[70px_1fr_1.4fr] gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Order</label>
                                                <input type="number" name="sort_order" min="0" value="{{ $template->sort_order }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Item</label>
                                                <input type="text" name="title" value="{{ $template->title }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Instruction</label>
                                                <input type="text" name="description" value="{{ $template->description }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Answer Type</label>
                                                <select name="input_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                    @foreach(['textarea' => 'Long Text', 'text' => 'Short Text', 'number' => 'Number', 'single_choice' => 'Single Choice', 'multi_choice' => 'Multiple Choice'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(($template->input_type ?? 'textarea') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Options</label>
                                                <textarea name="options" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="One per line">{{ implode("\n", $template->options ?? []) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center gap-3">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="is_required" value="1" @checked($template->is_required) class="rounded border-gray-300 text-blue-600">
                                                Required
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="rounded border-gray-300 text-blue-600">
                                                Active
                                            </label>
                                            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Save Item</button>
                                            <button type="submit" form="delete-template-{{ $template->id }}" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this template item? Existing jobs keep their copied checklist item.')">Delete</button>
                                        </div>
                                    </form>
                                    <form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('admin.service-categories.templates.destroy', $template) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center text-gray-600">
                    No field service categories yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
