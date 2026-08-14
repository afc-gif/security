@extends('admin.layout')

@section('title', 'Job Categories | ARTSCI Admin Console')

@push('styles')
<style>
    .category-modal {
        width: min(1120px, calc(100vw - 32px));
        max-height: calc(100vh - 48px);
        border: 0;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.24);
    }

    .category-modal::backdrop {
        background: rgba(15, 23, 42, 0.54);
    }

    .category-modal-shell {
        max-height: calc(100vh - 48px);
        overflow: auto;
        background: #f8fafc;
    }

    .field-preview {
        background: #f6f8fb;
        border: 1px solid #dce3ee;
        border-radius: 8px;
        padding: 16px;
    }

    .field-preview-card {
        border: 1px solid #dce3ee;
        border-radius: 8px;
        background: #fff;
        padding: 14px;
    }

    .field-preview-card + .field-preview-card {
        margin-top: 12px;
    }

    .modal-tab-panel[hidden] {
        display: none;
    }

    @media (max-width: 760px) {
        .category-modal {
            width: calc(100vw - 20px);
            max-height: calc(100vh - 20px);
        }

        .category-modal-shell {
            max-height: calc(100vh - 20px);
        }
    }
</style>
@endpush

@section('content')
@php
    $typeLabels = [
        'textarea' => 'Long Text',
        'text' => 'Short Text',
        'number' => 'Number',
        'single_choice' => 'Single Choice',
        'multi_choice' => 'Multiple Choice',
        'photo' => 'Photo Upload',
    ];
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Operations</div>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">Job Categories</h1>
                <p class="text-sm text-gray-600 mt-1 max-w-2xl">Choose a category to view or edit the checklist exactly as field staff will receive it.</p>
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
                    <p class="text-sm text-gray-600">Add the category here. The checklist can be edited from the category card.</p>
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

        @if($serviceCategories->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
                <h2 class="text-xl font-bold text-gray-900">No job categories yet</h2>
                <p class="text-sm text-gray-600 mt-1">Create your first category above, then add its default checklist.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach($serviceCategories as $category)
                    @php
                        $templates = $category->checklistTemplates;
                        $activeTemplates = $templates->where('is_active', true)->count();
                        $requiredTemplates = $templates->where('is_required', true)->count();
                    @endphp

                    <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold text-gray-900">{{ $category->name }}</h2>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $category->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $category->description ?: 'No description added yet.' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-lg font-bold text-gray-900">{{ $templates->count() }}</div>
                                <div class="text-xs font-semibold text-gray-500">Total Items</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-lg font-bold text-gray-900">{{ $activeTemplates }}</div>
                                <div class="text-xs font-semibold text-gray-500">Active</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-lg font-bold text-gray-900">{{ $requiredTemplates }}</div>
                                <div class="text-xs font-semibold text-gray-500">Required</div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold" data-modal-open="category-modal-{{ $category->id }}" data-modal-tab="preview">
                                View Checklist
                            </button>
                            <button type="button" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-semibold" data-modal-open="category-modal-{{ $category->id }}" data-modal-tab="edit">
                                Edit Checklist
                            </button>
                            <button type="button" class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 px-4 py-2 rounded-lg font-semibold" data-modal-open="category-modal-{{ $category->id }}" data-modal-tab="category">
                                Category Details
                            </button>
                        </div>
                    </section>

                    <dialog id="category-modal-{{ $category->id }}" class="category-modal">
                        <div class="category-modal-shell">
                            <div class="sticky top-0 z-10 border-b border-gray-200 bg-white px-5 py-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Job Category</div>
                                        <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $category->name }}</h2>
                                        <p class="text-sm text-gray-600 mt-1">{{ $category->description ?: 'No description added yet.' }}</p>
                                    </div>
                                    <button type="button" class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 px-4 py-2 rounded-lg font-semibold" data-modal-close>
                                        Close
                                    </button>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2" role="tablist" aria-label="{{ $category->name }} checklist tabs">
                                    <button type="button" class="modal-tab rounded-lg px-4 py-2 text-sm font-bold bg-gray-900 text-white" data-modal-target="category-modal-{{ $category->id }}" data-tab-button="preview">Field Preview</button>
                                    <button type="button" class="modal-tab rounded-lg px-4 py-2 text-sm font-bold bg-gray-100 text-gray-700" data-modal-target="category-modal-{{ $category->id }}" data-tab-button="edit">Edit Checklist</button>
                                    <button type="button" class="modal-tab rounded-lg px-4 py-2 text-sm font-bold bg-gray-100 text-gray-700" data-modal-target="category-modal-{{ $category->id }}" data-tab-button="category">Category Details</button>
                                </div>
                            </div>

                            <div class="p-5">
                                <div data-tab-panel="preview">
                                    <div class="field-preview">
                                        <div class="mb-4">
                                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Field View</div>
                                            <h3 class="text-xl font-bold text-gray-900 mt-1">{{ $category->name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">This is how staff will work through the steps on a job.</p>
                                        </div>

                                        @if($templates->where('is_active', true)->isEmpty())
                                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                                No active steps yet. Staff will see an empty list for this category.
                                            </div>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($templates->where('is_active', true) as $template)
                                                    @php
                                                        $inputType = $template->input_type ?? 'textarea';
                                                        $options = collect($template->options ?? []);
                                                    @endphp
                                                    <article class="field-preview-card">
                                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px]">
                                                            <div class="flex gap-3">
                                                                <div class="mt-0.5 flex h-8 min-w-8 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
                                                                    {{ $loop->iteration }}
                                                                </div>
                                                                <div class="min-w-0">
                                                                    <div class="font-bold text-gray-900">{{ $template->title }}</div>
                                                                    @if($template->description)
                                                                        <div class="text-sm text-gray-600 mt-1">{{ $template->description }}</div>
                                                                    @endif
                                                                    @if($template->is_required)
                                                                        <div class="mt-2 inline-flex rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">Required</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Status</label>
                                                                <select disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-700">
                                                                    <option>Pending</option>
                                                                    <option>Done</option>
                                                                    <option>Not Applicable</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Response</label>
                                                            @if($inputType === 'single_choice' && $options->isNotEmpty())
                                                                <select disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-700">
                                                                    <option>Select response</option>
                                                                    @foreach($options as $option)
                                                                        <option>{{ $option }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif($inputType === 'multi_choice' && $options->isNotEmpty())
                                                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                                    @foreach($options as $option)
                                                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                                                            <input type="checkbox" disabled class="rounded border-gray-300">
                                                                            <span>{{ $option }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @elseif($inputType === 'photo')
                                                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-100 px-3 py-4 text-sm font-semibold text-gray-600">
                                                                    Upload photo or open camera
                                                                </div>
                                                            @elseif($inputType === 'number')
                                                                <input type="number" disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" placeholder="Number response">
                                                            @elseif($inputType === 'text')
                                                                <input type="text" disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" placeholder="Short response">
                                                            @else
                                                                <textarea disabled rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" placeholder="Long response"></textarea>
                                                            @endif
                                                        </div>

                                                        <div class="mt-3">
                                                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Notes</label>
                                                            <input type="text" disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" placeholder="Optional notes">
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div data-tab-panel="edit" hidden>
                                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[360px_1fr]">
                                        <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                                            <h3 class="text-xl font-bold text-gray-900">Add Checklist Item</h3>
                                            <form method="POST" action="{{ route('admin.service-categories.templates.store', $category) }}" class="mt-4 grid grid-cols-1 gap-3">
                                                @csrf
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Checklist Item</label>
                                                    <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Confirm camera status">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Note</label>
                                                    <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional instruction for staff">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Response Type</label>
                                                    <select name="input_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                        @foreach($typeLabels as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Choices</label>
                                                    <textarea name="options" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Only for choice fields, one per line"></textarea>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                        <input type="checkbox" name="is_required" value="1" checked class="rounded border-gray-300 text-blue-600">
                                                        Required
                                                    </label>
                                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Add Item</button>
                                                </div>
                                            </form>
                                        </section>

                                        <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                                            <div class="flex flex-col gap-1 mb-4 sm:flex-row sm:items-center sm:justify-between">
                                                <h3 class="text-xl font-bold text-gray-900">Checklist Items</h3>
                                                <span class="text-sm text-gray-600">{{ $templates->count() }} item{{ $templates->count() === 1 ? '' : 's' }}</span>
                                            </div>

                                            @if($templates->isEmpty())
                                                <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                                    No checklist items yet.
                                                </div>
                                            @else
                                                <div class="space-y-3">
                                                    @foreach($templates as $template)
                                                        <details class="rounded-lg border border-gray-200 bg-gray-50">
                                                            <summary class="cursor-pointer list-none px-4 py-3">
                                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                                    <div class="min-w-0">
                                                                        <div class="flex flex-wrap items-center gap-2">
                                                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-gray-900 px-2 text-xs font-bold text-white">{{ $template->sort_order }}</span>
                                                                            <span class="font-bold text-gray-900">{{ $template->title }}</span>
                                                                            @if($template->is_required)
                                                                                <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">Required</span>
                                                                            @endif
                                                                            @unless($template->is_active)
                                                                                <span class="rounded-full border border-gray-200 bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600">Inactive</span>
                                                                            @endunless
                                                                        </div>
                                                                        <div class="mt-1 text-sm text-gray-600">{{ $typeLabels[$template->input_type ?? 'textarea'] ?? 'Long Text' }}</div>
                                                                    </div>
                                                                    <span class="text-sm font-bold text-blue-700">Edit</span>
                                                                </div>
                                                            </summary>
                                                            <form method="POST" action="{{ route('admin.service-categories.templates.update', $template) }}" class="border-t border-gray-200 p-4 grid grid-cols-1 gap-3">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[90px_1fr]">
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
                                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
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
                                                                    <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Save Item</button>
                                                                    <button type="submit" form="delete-template-{{ $template->id }}" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this template item? Existing jobs keep their copied checklist item.')">Delete</button>
                                                                </div>
                                                            </form>
                                                            <form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('admin.service-categories.templates.destroy', $template) }}" class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </details>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </section>
                                    </div>
                                </div>

                                <div data-tab-panel="category" hidden>
                                    <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                                        <h3 class="text-xl font-bold text-gray-900">Category Details</h3>
                                        <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="mt-4 grid grid-cols-1 gap-3">
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
                                        <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" class="mt-5 border-t border-gray-200 pt-4">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Delete this category? Categories already used by jobs cannot be deleted.')">Delete Category</button>
                                        </form>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </dialog>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /* ── Tab switching ─────────────────────────────────────────── */
    const setActiveTab = (modal, tabName) => {
        modal.querySelectorAll('[data-tab-panel]').forEach(panel => {
            panel.hidden = panel.dataset.tabPanel !== tabName;
        });
        modal.querySelectorAll('[data-tab-button]').forEach(btn => {
            const on = btn.dataset.tabButton === tabName;
            btn.classList.toggle('bg-gray-900', on);
            btn.classList.toggle('text-white',  on);
            btn.classList.toggle('bg-gray-100', !on);
            btn.classList.toggle('text-gray-700', !on);
        });
    };

    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalOpen);
            if (!modal) return;
            setActiveTab(modal, btn.dataset.modalTab || 'preview');
            modal.showModal ? modal.showModal() : modal.setAttribute('open','open');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('dialog')?.close());
    });

    document.querySelectorAll('[data-tab-button]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalTarget);
            if (modal) setActiveTab(modal, btn.dataset.tabButton);
        });
    });

    document.querySelectorAll('.category-modal').forEach(modal => {
        modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });
    });

    /* ── Fixed-position snackbar toast ────────────────────────── */
    const snackbar = document.createElement('div');
    snackbar.style.cssText = [
        'position:fixed',
        'bottom:24px',
        'right:24px',
        'z-index:99999',
        'display:flex',
        'flex-direction:column',
        'gap:8px',
        'pointer-events:none',
        'max-width:320px',
    ].join(';');
    document.body.appendChild(snackbar);

    function showToast(message, isError) {
        const toast = document.createElement('div');
        toast.style.cssText = [
            'padding:12px 18px',
            'border-radius:10px',
            'font-size:0.875rem',
            'font-weight:600',
            'box-shadow:0 4px 16px rgba(0,0,0,0.14)',
            'opacity:0',
            'transform:translateY(10px)',
            'transition:opacity .25s ease,transform .25s ease',
            isError
                ? 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;'
                : 'background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;',
        ].join(';');
        toast.textContent = message;
        snackbar.appendChild(toast);

        requestAnimationFrame(() => requestAnimationFrame(() => {
            toast.style.opacity   = '1';
            toast.style.transform = 'translateY(0)';
        }));

        setTimeout(() => {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* ── AJAX form submission (modal forms only) ───────────────── */
    function submitFormAjax(form, btn) {
        const originalText = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        const data     = new FormData(form);
        const method   = (data.get('_method') || form.method || 'POST').toUpperCase();
        const isDelete = method === 'DELETE';
        if (isDelete) btn.textContent = 'Deleting…';

        fetch(form.action, {
            method : 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body   : data,
        })
        .then(async res => {
            const json = await res.json().catch(() => ({}));

            if (res.ok && json.success !== false) {
                showToast(json.message || 'Saved successfully.');
                if (isDelete) {
                    const row = form.closest('details');
                    if (row) row.remove();
                } else if (form.dataset.resetAfter === 'true') {
                    form.reset();
                }
            } else {
                const msg = json.message
                    || (json.errors ? Object.values(json.errors).flat().join(' ') : null)
                    || 'Something went wrong. Please try again.';
                showToast(msg, true);
            }
        })
        .catch(() => showToast('Network error. Please try again.', true))
        .finally(() => {
            btn.disabled    = false;
            btn.textContent = originalText;
        });
    }

    /* Attach to every form inside a dialog */
    document.querySelectorAll('dialog form').forEach(form => {
        if (form.closest('[data-tab-panel="edit"]') && !form.querySelector('[name="_method"]')) {
            form.dataset.resetAfter = 'true';
        }
        form.addEventListener('submit', e => {
            e.preventDefault();
            const btn = e.submitter || form.querySelector('[type="submit"]');
            submitFormAjax(form, btn);
        });
    });
})();
</script>
@endpush
