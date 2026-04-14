@extends('admin.layout')

@section('title', 'Create Task | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Task</h1>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.tasks.store') }}" class="space-y-6">
                @csrf

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
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Field Staff *</label>
                        <select name="assigned_to" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Select field staff</option>
                            @foreach($fieldStaff as $staff)
                                <option value="{{ $staff->id }}" @selected((string) old('assigned_to') === (string) $staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Linked Work Type *</label>
                        <select id="assignableType" name="assignable_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Select type</option>
                            <option value="{{ \App\Models\Inspection::class }}" @selected(old('assignable_type') === \App\Models\Inspection::class)>Inspection</option>
                            <option value="{{ \App\Models\Project::class }}" @selected(old('assignable_type') === \App\Models\Project::class)>Project</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Choose whether this task belongs to an inspection or a project.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Linked Inspection or Project *</label>
                        <select id="assignableId" name="assignable_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Select inspection or project</option>
                            <optgroup label="Inspections">
                                @foreach($inspections as $inspection)
                                    <option value="{{ $inspection->id }}" data-type="{{ \App\Models\Inspection::class }}" @selected((string) old('assignable_id') === (string) $inspection->id && old('assignable_type') === \App\Models\Inspection::class)>
                                        Inspection — {{ $inspection->inspection_code }} — {{ $inspection->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Projects">
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" data-type="{{ \App\Models\Project::class }}" @selected((string) old('assignable_id') === (string) $project->id && old('assignable_type') === \App\Models\Project::class)>
                                        Project — {{ $project->project_code }} — {{ $project->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">After choosing the linked work type, select the matching inspection or project record.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="datetime-local" name="due_date" value="{{ old('due_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                            <option value="in_progress" @selected(old('status') === 'in_progress')>In Progress</option>
                            <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">No priority</option>
                            <option value="low" @selected(old('priority') === 'low')>Low</option>
                            <option value="medium" @selected(old('priority') === 'medium')>Medium</option>
                            <option value="high" @selected(old('priority') === 'high')>High</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Save Task</button>
                    <a href="{{ route('admin.tasks.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const typeSelect = document.getElementById('assignableType');
        const recordSelect = document.getElementById('assignableId');
        if (!typeSelect || !recordSelect) return;

        const syncRecordOptions = () => {
            const selectedType = typeSelect.value;
            Array.from(recordSelect.options).forEach((option) => {
                if (!option.dataset.type) return;
                option.disabled = selectedType !== '' && option.dataset.type !== selectedType;
            });

            const selectedOption = recordSelect.selectedOptions[0];
            if (selectedOption && selectedOption.disabled) {
                recordSelect.value = '';
            }
        };

        typeSelect.addEventListener('change', syncRecordOptions);
        syncRecordOptions();
    })();
</script>
@endpush
