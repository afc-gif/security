@extends('admin.layout')

@section('title', 'Inspection Review | ARTSCI Admin Console')

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
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 font-medium">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($inspection->review_status === 'returned')
            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5 text-amber-900 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-amber-900 text-base">
                    <span>⚠️ Returned for Additional Details</span>
                </div>
                <div class="mt-2 text-sm text-amber-800">
                    <strong class="font-semibold text-amber-950">Return Reason:</strong> {{ $inspection->return_reason ?: $inspection->review_notes }}
                </div>
                <div class="mt-2 text-xs text-amber-700">
                    Returned by {{ $inspection->returnedBy?->name ?? 'Admin' }} on {{ $inspection->returned_at?->format('d M Y H:i') ?? '—' }}. Awaiting resubmission by Field Staff.
                </div>
            </div>
        @endif

        <!-- Inspection Header / Summary -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Inspection Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Customer / Client</div>
                    <div class="text-gray-900 font-bold">{{ $inspection->client?->company_name ?: $inspection->client?->client_name ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned Field Staff</div>
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
                    <div class="text-gray-900 font-medium">{{ $inspection->priority ? ucfirst($inspection->priority) : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    @php
                        $statusClasses = match($inspection->status) {
                            'completed' => 'bg-green-100 text-green-800',
                            'returned' => 'bg-amber-100 text-amber-800',
                            'assigned' => 'bg-blue-100 text-blue-800',
                            default => 'bg-yellow-100 text-yellow-800',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold {{ $statusClasses }}">
                        {{ ucfirst($inspection->status) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Review Status</div>
                    @php
                        $reviewStatus = $inspection->review_status ?? 'pending_review';
                        $reviewClasses = match($reviewStatus) {
                            'approved' => 'bg-green-100 text-green-800',
                            'returned' => 'bg-amber-100 text-amber-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            default => 'bg-yellow-100 text-yellow-800',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold {{ $reviewClasses }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($reviewStatus)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $inspection->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <!-- Submitted Field Report -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Submitted Field Report</h2>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Findings</div>
                    <div class="text-gray-900 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-200 min-h-[50px]">{{ $inspection->findings ?: 'No findings recorded yet.' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Risks Identified</div>
                    <div class="text-gray-900 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-200 min-h-[50px]">{{ $inspection->risks_identified ?: 'No risks identified.' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Recommendations</div>
                    <div class="text-gray-900 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-200 min-h-[50px]">{{ $inspection->recommendations ?: 'No recommendations submitted.' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Submitted At</div>
                    <div class="text-gray-900 font-medium">{{ $inspection->submitted_at?->format('d M Y H:i') ?? 'Not submitted yet' }}</div>
                </div>
            </div>
        </div>

        <!-- Submitted Checklist Section -->
        @php
            $checklist = $inspection->effective_checklist_items;
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Submitted Checklist Responses</h2>
                @if($checklist->count() > 0)
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $checklist->where('status', 'done')->count() }} / {{ $checklist->count() }} Completed
                    </span>
                @endif
            </div>

            @if($checklist->count() === 0)
                <div class="text-gray-500 text-sm bg-gray-50 p-4 rounded-lg border border-gray-200">
                    No checklist template attached to this inspection category.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($checklist as $index => $item)
                        @php
                            $itemStatusClasses = match($item->status) {
                                'done' => 'bg-green-100 text-green-800 border-green-200',
                                'not_applicable' => 'bg-gray-100 text-gray-700 border-gray-200',
                                default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            };
                            $itemStatusLabel = match($item->status) {
                                'done' => 'Completed',
                                'not_applicable' => 'Not Applicable',
                                default => 'Pending / Needs Attention',
                            };
                        @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Step {{ $index + 1 }}</div>
                                    <div class="font-bold text-gray-900 text-base mt-0.5">{{ $item->title }}</div>
                                    @if($item->description)
                                        <div class="text-xs text-gray-600 mt-1">{{ $item->description }}</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold border {{ $itemStatusClasses }}">
                                    {{ $itemStatusLabel }}
                                </span>
                            </div>

                            @if($item->response || $item->notes)
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-white p-3 rounded-lg border border-gray-200">
                                    @if($item->response)
                                        <div>
                                            <div class="font-bold text-gray-500 uppercase tracking-wide text-[10px]">Field Staff Answer</div>
                                            <div class="text-gray-900 font-semibold mt-0.5">{{ $item->response }}</div>
                                        </div>
                                    @endif
                                    @if($item->notes)
                                        <div>
                                            <div class="font-bold text-gray-500 uppercase tracking-wide text-[10px]">Notes / Measurements</div>
                                            <div class="text-gray-900 mt-0.5 whitespace-pre-line">{{ $item->notes }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($item->media && $item->media->count() > 0)
                                <div class="mt-3">
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Checklist Photos</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach($item->media as $cMedia)
                                            @php
                                                $mediaUrl = \App\Support\ImageUrl::url($cMedia->file_path);
                                            @endphp
                                            @if($mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="block border rounded-lg overflow-hidden group">
                                                    <img src="{{ $mediaUrl }}" alt="Checklist evidence" class="h-24 w-full object-cover group-hover:opacity-90 transition">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Evidence Files / Media -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Inspection Evidence Photos & Files</h2>
            @if($inspection->media->count() === 0)
                <div class="text-gray-500 text-sm">No general evidence files uploaded yet.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($inspection->media as $media)
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            @php
                                $mediaUrl = \App\Support\ImageUrl::url($media->file_path);
                            @endphp
                            @if($mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="block">
                                    <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Evidence' }}" class="mb-2 h-32 w-full rounded-lg object-cover">
                                    <div class="font-semibold text-sm text-blue-700 hover:underline truncate">{{ $media->file_name ?? basename($media->file_path) }}</div>
                                </a>
                            @else
                                <div class="font-semibold text-sm text-gray-800 truncate">{{ $media->file_name ?? basename((string) $media->file_path) }}</div>
                            @endif
                            <div class="text-xs text-gray-500 mt-1">
                                Uploaded by {{ $media->uploader?->name ?? 'Field Staff' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Admin Review Actions -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Review Actions</h2>
            
            <form method="POST" action="{{ route('admin.inspections.review', $inspection) }}" class="space-y-4" id="reviewForm">
                @csrf
                <div>
                    <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-1">Review Notes / Admin Remarks</label>
                    <textarea id="review_notes" name="review_notes" rows="3" placeholder="Optional notes for approval or internal record..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('review_notes') }}</textarea>
                </div>

                <div id="returnReasonContainer" class="hidden">
                    <label for="return_reason" class="block text-sm font-bold text-amber-900 mb-1">Return Reason / Required Instructions <span class="text-red-600">*</span></label>
                    <textarea id="return_reason" name="return_reason" rows="3" placeholder="Specify exact photos, measurements, or corrections Field Staff must provide..." class="w-full border border-amber-300 bg-amber-50/50 rounded-lg px-3 py-2 text-sm">{{ old('return_reason') }}</textarea>
                    <span class="text-xs text-amber-700 mt-1 block">Field staff will see this message directly in their workspace to update their report.</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" name="review_status" value="approved" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm transition">
                        ✓ Approve Inspection
                    </button>
                    
                    <button type="button" id="toggleReturnBtn" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm transition">
                        ↩ Return for More Details
                    </button>

                    <button type="submit" name="review_status" value="returned" id="confirmReturnBtn" class="hidden bg-amber-700 hover:bg-amber-800 text-white px-6 py-2.5 rounded-lg font-bold text-sm transition">
                        Submit Return to Field Staff
                    </button>

                    <button type="submit" name="review_status" value="rejected" onclick="return confirm('Are you sure you want to reject this inspection?')" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition">
                        ✕ Reject
                    </button>
                </div>
            </form>
        </div>

        <!-- Revision & Submission History Log -->
        @if($inspection->revisions && $inspection->revisions->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Inspection Revision Audit History</h2>
                <div class="space-y-4">
                    @foreach($inspection->revisions as $rev)
                        @php
                            $revBadge = match($rev->action) {
                                'approved' => 'bg-green-100 text-green-800',
                                'returned' => 'bg-amber-100 text-amber-800',
                                'submitted' => 'bg-blue-100 text-blue-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/70">
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ $revBadge }}">
                                    {{ ucfirst($rev->action) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $rev->created_at?->format('d M Y H:i') }}</span>
                            </div>
                            <div class="text-xs text-gray-700 font-medium">By: {{ $rev->user?->name ?? 'User' }}</div>
                            @if($rev->return_reason)
                                <div class="mt-2 text-xs text-amber-900 bg-amber-50 p-2 rounded border border-amber-200">
                                    <strong>Return Reason:</strong> {{ $rev->return_reason }}
                                </div>
                            @endif
                            @if($rev->admin_notes && $rev->admin_notes !== $rev->return_reason)
                                <div class="mt-1 text-xs text-gray-600">
                                    <strong>Admin Notes:</strong> {{ $rev->admin_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Project Conversion -->
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
    </div>
</div>

<script>
(function() {
    const toggleReturnBtn = document.getElementById('toggleReturnBtn');
    const returnReasonContainer = document.getElementById('returnReasonContainer');
    const confirmReturnBtn = document.getElementById('confirmReturnBtn');
    const returnReasonInput = document.getElementById('return_reason');

    if (toggleReturnBtn && returnReasonContainer && confirmReturnBtn) {
        toggleReturnBtn.addEventListener('click', function() {
            returnReasonContainer.classList.remove('hidden');
            confirmReturnBtn.classList.remove('hidden');
            toggleReturnBtn.classList.add('hidden');
            returnReasonInput.setAttribute('required', 'required');
            returnReasonInput.focus();
        });
    }
})();
</script>
@endsection
