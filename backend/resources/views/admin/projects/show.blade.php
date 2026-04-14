@extends('admin.layout')

@section('title', 'Project Details | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->project_code }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $project->title }}</p>
            </div>
            <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Projects
            </a>
        </div>

        @if (session('success'))
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

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Client</div>
                    <div class="text-gray-900 font-semibold">{{ $project->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $project->status === 'completed' ? 'bg-green-100 text-green-800' : ($project->status === 'ongoing' ? 'bg-blue-100 text-blue-800' : ($project->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 text-gray-700')) }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($project->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Progress</div>
                    <div class="flex items-center gap-3">
                        <div class="h-2 flex-1 rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-full bg-blue-600" style="width: {{ min(100, max(0, (int) ($project->progress_percentage ?? 0))) }}%;"></div>
                        </div>
                        <div class="text-gray-900 font-semibold whitespace-nowrap">{{ $project->progress_percentage ?? 0 }}%</div>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Location</div>
                    <div class="text-gray-900">{{ $project->location ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Priority</div>
                    <div class="text-gray-900">{{ $project->priority ? ucfirst($project->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Start Date</div>
                    <div class="text-gray-900">{{ $project->start_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Deadline</div>
                    <div class="text-gray-900">{{ $project->deadline?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned Manager</div>
                    <div class="text-gray-900">{{ $project->manager?->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned Field Staff</div>
                    <div class="text-gray-900">{{ $project->fieldStaff?->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $project->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Description</h2>
            <div class="text-gray-900 whitespace-pre-line">{{ $project->description ?: '—' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Linked Inspection</h2>
            @if($project->inspection)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $project->inspection->inspection_code }}</div>
                        <div class="text-sm text-gray-600">{{ $project->inspection->title }}</div>
                    </div>
                    <a href="{{ route('admin.inspections.show', $project->inspection) }}" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                        View Inspection
                    </a>
                </div>
            @else
                <div class="text-gray-600">This project was created manually and is not linked to an inspection.</div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Update Timeline</h2>
            @if($project->updates->count() === 0)
                <div class="text-gray-600">No project updates submitted yet.</div>
            @else
                <div class="space-y-4">
                    @foreach($project->updates as $update)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $update->summary ?: 'Project update' }}</div>
                                    <div class="text-sm text-gray-600">
                                        Submitted by {{ $update->user?->name ?? '—' }}
                                        on {{ $update->created_at?->format('d M Y H:i') ?? '—' }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-700 whitespace-nowrap">
                                    Work date: {{ $update->work_date?->format('d M Y') ?? '—' }}
                                </div>
                            </div>

                            @php($updateReviewStatus = $update->review_status ?? 'pending_review')
                            <div class="mt-3 rounded-lg border border-gray-200 bg-white p-3">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Review Status</div>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $updateReviewStatus === 'reviewed' ? 'bg-green-100 text-green-800' : ($updateReviewStatus === 'needs_correction' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ str_replace('_', ' ', \Illuminate\Support\Str::title($updateReviewStatus)) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Reviewed by {{ $update->reviewedBy?->name ?? '—' }}
                                        @if($update->reviewed_at)
                                            on {{ $update->reviewed_at->format('d M Y H:i') }}
                                        @endif
                                    </div>
                                </div>

                                @if($updateReviewStatus === 'pending_review')
                                    <form method="POST" action="{{ route('admin.project-updates.review', $update) }}" class="mt-3 space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Review Notes</label>
                                            <textarea name="review_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('review_notes') }}</textarea>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <button type="submit" name="review_status" value="reviewed" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                                                Mark Reviewed
                                            </button>
                                            <button type="submit" name="review_status" value="needs_correction" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                                                Needs Correction
                                            </button>
                                        </div>
                                    </form>
                                @elseif($update->review_notes)
                                    <div class="mt-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Review Notes</div>
                                        <div class="text-gray-900 whitespace-pre-line">{{ $update->review_notes }}</div>
                                    </div>
                                @endif
                            </div>

                            @if($update->progress_percentage !== null)
                                <div class="mt-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Reported Progress</div>
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-full bg-blue-600" style="width: {{ min(100, max(0, (int) $update->progress_percentage)) }}%;"></div>
                                        </div>
                                        <div class="text-gray-900 font-semibold whitespace-nowrap">{{ $update->progress_percentage }}%</div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 gap-4 mt-4">
                                @if($update->work_done)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Work Done</div>
                                        <div class="text-gray-900 whitespace-pre-line">{{ $update->work_done }}</div>
                                    </div>
                                @endif
                                @if($update->materials_used)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Materials Used</div>
                                        <div class="text-gray-900 whitespace-pre-line">{{ $update->materials_used }}</div>
                                    </div>
                                @endif
                                @if($update->issues_encountered)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Issues Encountered</div>
                                        <div class="text-gray-900 whitespace-pre-line">{{ $update->issues_encountered }}</div>
                                    </div>
                                @endif
                                @if($update->next_step)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Next Step</div>
                                        <div class="text-gray-900 whitespace-pre-line">{{ $update->next_step }}</div>
                                    </div>
                                @endif
                            </div>

                            @if($update->media->count())
                                <div class="mt-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Uploaded Media</div>
                                    <div class="grid grid-cols-1 gap-2">
                                        @foreach($update->media as $media)
                                            <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                                @if($mediaUrl)
                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 hover:text-blue-900">
                                                        {{ $media->file_name ?? basename($media->file_path) }}
                                                    </a>
                                                @else
                                                    <div class="font-semibold text-gray-800">{{ $media->file_name ?? basename((string) $media->file_path) }}</div>
                                                    <div class="text-sm text-red-700 mt-1">File unavailable</div>
                                                @endif
                                                <div class="text-sm text-gray-600 mt-1">
                                                    Uploaded by {{ $media->uploader?->name ?? '—' }}
                                                    @if($media->file_type)
                                                        · {{ $media->file_type }}
                                                    @endif
                                                    @if($media->file_size)
                                                        · {{ number_format($media->file_size / 1024, 1) }} KB
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
