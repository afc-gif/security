@extends('admin.layout')

@section('title', 'Job Categories | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Operations</div>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">Job Categories</h1>
                <p class="text-sm text-gray-600 mt-1 max-w-2xl">Keep the field workflow simple: create the job type once, then define the default checklist staff should follow.</p>
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

        <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
            <div class="flex flex-col gap-1 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">New Job Category</h2>
                    <p class="text-sm text-gray-600">Add one clear category for a type of field work.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.service-categories.store') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(180px,0.9fr)_minmax(240px,1.4fr)_auto] lg:items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="CCTV Maintenance">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="What kind of work belongs here?">
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Active
                    </label>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Add Category</button>
                </div>
            </form>
        </section>

        <div class="space-y-4">
            @forelse($serviceCategories as $category)
                @php
                    $templates = $category->checklistTemplates;
                    $activeTemplates = $templates->where('is_active', true)->count();
                    $requiredTemplates = $templates->where('is_required', true)->count();
                    $typeLabels = [
                        'textarea' => 'Long Text',
                        'text' => 'Short Text',
                        'number' => 'Number',
                        'single_choice' => 'Single Choice',
                        'multi_choice' => 'Multiple Choice',
                    ];
                @endphp

                <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold text-gray-900">{{ $category->name }}</h2>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $category->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $category->description ?: 'No description added yet.' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-gray-600">
                                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1">{{ $templates->count() }} checklist item{{ $templates->count() === 1 ? '' : 's' }}</span>
                                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1">{{ $activeTemplates }} active</span>
                                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1">{{ $requiredTemplates }} required</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-2">
                            <details class="rounded-lg border border-gray-200 bg-gray-50">
                                <summary class="cursor-pointer list-none px-4 py-3 font-bold text-gray-900">
                                    Edit Category Details
                                </summary>
                                <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="border-t border-gray-200 p-4 grid grid-cols-1 gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                                        <input type="text" name="name" value="{{ $category->name }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                        <input type="text" name="description" value="{{ $category->description }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                            <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="rounded border-gray-300 text-blue-600">
                                            Active
                                        </label>
                                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Save Category</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" class="border-t border-gray-200 px-4 py-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this category? Categories already used by jobs cannot be deleted.')">Delete Category</button>
                                </form>
                            </details>

                            <details class="rounded-lg border border-blue-100 bg-blue-50/40">
                                <summary class="cursor-pointer list-none px-4 py-3 font-bold text-gray-900">
                                    Manage Default Checklist
                                </summary>
                                <div class="border-t border-blue-100 p-4">
                                    <form method="POST" action="{{ route('admin.service-categories.templates.store', $category) }}" class="grid grid-cols-1 gap-3">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-[1fr_170px] gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Checklist Item</label>
                                                <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Confirm camera status">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Response Type</label>
                                                <select name="input_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                    <option value="textarea">Long Text</option>
                                                    <option value="text">Short Text</option>
                                                    <option value="number">Number</option>
                                                    <option value="single_choice">Single Choice</option>
                                                    <option value="multi_choice">Multiple Choice</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Field Note</label>
                                                <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional instruction for staff">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Choices</label>
                                                <textarea name="options" rows="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Only for choice fields, one per line"></textarea>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="is_required" value="1" checked class="rounded border-gray-300 text-blue-600">
                                                Required
                                            </label>
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Add Checklist Item</button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 bg-gray-50/70 px-5 py-4">
                        <div class="flex flex-col gap-1 mb-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-base font-bold text-gray-900">Default Checklist</h3>
                            <span class="text-sm text-gray-600">{{ $templates->count() }} item{{ $templates->count() === 1 ? '' : 's' }}</span>
                        </div>

                        @if($templates->isEmpty())
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                No default checklist yet. Add the common steps field staff should complete for this category.
                            </div>
                        @else
                            <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                                @foreach($templates as $template)
                                    <div class="p-4">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-gray-900 px-2 text-xs font-bold text-white">{{ $template->sort_order }}</span>
                                                    <h4 class="font-bold text-gray-900">{{ $template->title }}</h4>
                                                    @if($template->is_required)
                                                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">Required</span>
                                                    @endif
                                                    @unless($template->is_active)
                                                        <span class="rounded-full border border-gray-200 bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600">Inactive</span>
                                                    @endunless
                                                </div>
                                                <p class="mt-1 text-sm text-gray-600">{{ $template->description ?: 'No field note.' }}</p>
                                                <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold text-gray-500">
                                                    <span>{{ $typeLabels[$template->input_type ?? 'textarea'] ?? 'Long Text' }}</span>
                                                    @if(!empty($template->options))
                                                        <span>{{ count($template->options) }} choice{{ count($template->options) === 1 ? '' : 's' }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <details class="w-full lg:w-80 rounded-lg border border-gray-200 bg-gray-50">
                                                <summary class="cursor-pointer list-none px-3 py-2 text-sm font-bold text-gray-800">
                                                    Edit Item
                                                </summary>
                                                <form method="POST" action="{{ route('admin.service-categories.templates.update', $template) }}" class="border-t border-gray-200 p-3 grid grid-cols-1 gap-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="grid grid-cols-[80px_1fr] gap-3">
                                                        <div>
                                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Order</label>
                                                            <input type="number" name="sort_order" min="0" value="{{ $template->sort_order }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Item</label>
                                                            <input type="text" name="title" value="{{ $template->title }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Field Note</label>
                                                        <input type="text" name="description" value="{{ $template->description }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Response Type</label>
                                                        <select name="input_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                            @foreach($typeLabels as $value => $label)
                                                                <option value="{{ $value }}" @selected(($template->input_type ?? 'textarea') === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Choices</label>
                                                        <textarea name="options" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="One per line">{{ implode("\n", $template->options ?? []) }}</textarea>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                            <input type="checkbox" name="is_required" value="1" @checked($template->is_required) class="rounded border-gray-300 text-blue-600">
                                                            Required
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                            <input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="rounded border-gray-300 text-blue-600">
                                                            Active
                                                        </label>
                                                    </div>
                                                    <div class="flex flex-wrap gap-2">
                                                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Save</button>
                                                        <button type="submit" form="delete-template-{{ $template->id }}" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this template item? Existing jobs keep their copied checklist item.')">Delete</button>
                                                    </div>
                                                </form>
                                                <form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('admin.service-categories.templates.destroy', $template) }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </details>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
                    <h2 class="text-xl font-bold text-gray-900">No job categories yet</h2>
                    <p class="text-sm text-gray-600 mt-1">Create your first category above, then add its default checklist.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
