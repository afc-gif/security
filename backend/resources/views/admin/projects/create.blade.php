@extends('admin.layout')

@section('title', 'Create Project | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Project</h1>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-6">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                        <select name="client_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Select client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>{{ $client->client_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="not_started" @selected(old('status', 'not_started') === 'not_started')>Not Started</option>
                            <option value="ongoing" @selected(old('status') === 'ongoing')>Ongoing</option>
                            <option value="on_hold" @selected(old('status') === 'on_hold')>On Hold</option>
                            <option value="completed" @selected(old('status') === 'completed')>Completed</option>
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Manager</label>
                        <select name="assigned_manager_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Unassigned</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((string) old('assigned_manager_id') === (string) $manager->id)>{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Field Staff</label>
                        <select name="assigned_field_staff_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Unassigned</option>
                            @foreach($fieldStaff as $staff)
                                <option value="{{ $staff->id }}" @selected((string) old('assigned_field_staff_id') === (string) $staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                        <input type="date" name="deadline" value="{{ old('deadline') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Save Project</button>
                    <a href="{{ route('admin.projects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
