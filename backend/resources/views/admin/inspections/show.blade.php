@extends('admin.layout')

@section('title', 'Inspection Details | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $inspection->inspection_code }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $inspection->title }}</p>
            </div>
            <a href="{{ route('admin.inspections.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Inspections
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Client</div>
                    <div class="text-gray-900 font-semibold">{{ $inspection->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned To</div>
                    <div class="text-gray-900 font-semibold">{{ $inspection->assignedUser?->name ?? 'Unassigned' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Location</div>
                    <div class="text-gray-900">{{ $inspection->location }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Inspection Type</div>
                    <div class="text-gray-900">{{ $inspection->inspection_type ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Scheduled Date</div>
                    <div class="text-gray-900">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Priority</div>
                    <div class="text-gray-900">{{ $inspection->priority ? ucfirst($inspection->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $inspection->status === 'completed' ? 'bg-green-100 text-green-800' : ($inspection->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($inspection->status) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $inspection->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Submitted Report</h2>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Findings</div>
                    <div class="text-gray-900 whitespace-pre-line">{{ $inspection->findings ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Risks Identified</div>
                    <div class="text-gray-900 whitespace-pre-line">{{ $inspection->risks_identified ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Recommendations</div>
                    <div class="text-gray-900 whitespace-pre-line">{{ $inspection->recommendations ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Submitted At</div>
                    <div class="text-gray-900">{{ $inspection->submitted_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Conversion</h2>
            @if($inspection->project)
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <div class="text-gray-900 font-semibold">This inspection is linked to project {{ $inspection->project->project_code }}.</div>
                    <a href="{{ route('admin.projects.show', $inspection->project) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition mt-3">
                        View Project
                    </a>
                </div>
            @else
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div>
                        <div class="font-semibold text-gray-900">Create a project from this inspection.</div>
                        <div class="text-sm text-gray-600 mt-1">The inspection will stay intact and the project will keep a link back to it.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.inspections.convert-to-project', $inspection) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Convert this inspection to a project?')" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                            Convert to Project
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Evidence Files</h2>
            @if($inspection->media->count() === 0)
                <div class="text-gray-600">No evidence files uploaded yet.</div>
            @else
                <div class="grid grid-cols-1 gap-3">
                    @foreach($inspection->media as $media)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            @if($media->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($media->file_path))
                                <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 hover:text-blue-900">
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
            @endif
        </div>
    </div>
</div>
@endsection
