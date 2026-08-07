@extends('admin.layout')

@section('title', 'Create Job Request | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Job Request</h1>
        <div class="mb-6">
            <a href="{{ route('admin.service-categories.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold">
                Manage Field Categories & Checklists
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.job-requests.store') }}" class="space-y-6">
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
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Client service request">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Submission Deadline</label>
                    <input type="datetime-local" name="due_date" value="{{ old('due_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                    Saved job requests go to the field coordinator first. The coordinator assigns them to staff or releases them for open claim.
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Checklist Items</label>
                    <textarea name="additional_checklist" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="One checklist item per line">{{ old('additional_checklist') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Optional. These lines will be added to each selected category item for this job.</p>
                </div>

                <div>
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Service Categories *</label>
                            <p class="text-xs text-gray-500 mt-1">Each selected service category will become one category item on this job request.</p>
                        </div>
                    </div>

                    @if($serviceCategories->count() === 0)
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-900">
                            No active service categories are available.
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($serviceCategories as $category)
                                <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 cursor-pointer hover:bg-blue-50 hover:border-blue-200">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array((string) $category->id, old('categories', []), true)) class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600">
                                    <span>
                                        <span class="block font-semibold text-gray-900">{{ $category->name }}</span>
                                        @if($category->description)
                                            <span class="block text-sm text-gray-600 mt-1">{{ $category->description }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold" @disabled($serviceCategories->count() === 0)>Save Job Request</button>
                    <a href="{{ route('admin.job-requests.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
