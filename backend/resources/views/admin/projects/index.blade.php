@extends('admin.layout')

@section('title', 'Projects | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
                <p class="text-sm text-gray-600 mt-1">Track client projects converted from inspections or created manually.</p>
            </div>
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                Create Project
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if($projects->count() === 0)
                <div class="p-10 text-center text-gray-600">
                    No projects yet. Create your first project.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Project Code</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Inspection</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Progress</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Deadline</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($projects as $project)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900 whitespace-nowrap">{{ $project->project_code }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-900 max-w-[260px] truncate">{{ $project->title }}</div>
                                        @if($project->location)
                                            <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $project->location }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $project->client?->client_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $project->inspection?->inspection_code ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $project->status === 'completed' ? 'bg-green-100 text-green-800' : ($project->status === 'ready_for_review' ? 'bg-purple-100 text-purple-800' : ($project->status === 'ongoing' ? 'bg-blue-100 text-blue-800' : ($project->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 text-gray-700'))) }}">
                                            {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $project->progress_percentage ?? 0 }}%</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $project->deadline?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.projects.show', $project) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
