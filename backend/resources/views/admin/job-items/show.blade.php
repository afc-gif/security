@extends('admin.layout')

@section('title', 'Job Item Review | ARTSCI Admin Console')

@section('content')
@php
    $reviewRequirementRows = old('requirements', $latestAttempt?->requirements?->count()
        ? $latestAttempt->requirements->map(fn ($requirement) => [
            'include' => '1',
            'type' => $requirement->type,
            'name' => $requirement->name,
            'quantity' => $requirement->quantity,
            'notes' => $requirement->notes,
        ])->values()->all()
        : [['include' => '1', 'type' => 'material', 'name' => '', 'quantity' => '', 'notes' => '']]
    );
    $mediaByChecklist = $latestAttempt?->media?->whereNotNull('job_checklist_item_id')->groupBy('job_checklist_item_id') ?? collect();
    $generalMedia = $latestAttempt?->media?->whereNull('job_checklist_item_id') ?? collect();
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Job Item Review</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $jobItem->jobRequest?->title ?? 'Job request unavailable' }}</p>
            </div>
            <a href="{{ $jobItem->jobRequest ? route('admin.job-requests.show', $jobItem->jobRequest) : route('admin.job-requests.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Job Request
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
                    <div class="text-gray-900 font-semibold">{{ $jobItem->jobRequest?->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Category</div>
                    <div class="text-gray-900 font-semibold">{{ $jobItem->serviceCategory?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Claimed By</div>
                    <div class="text-gray-900">{{ $jobItem->claimer?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ match ($jobItem->status) { 'open', 'reopened' => 'bg-blue-100 text-blue-800', 'claimed' => 'bg-blue-100 text-blue-800', 'submitted', 'pending_admin_review' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'returned' => 'bg-orange-100 text-orange-800', 'overdue', 'rejected' => 'bg-red-100 text-red-800', 'closed' => 'bg-gray-300 text-gray-800', default => 'bg-gray-200 text-gray-700' } }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($jobItem->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Due Date</div>
                    <div class="{{ $jobItem->isOverdue() ? 'font-semibold text-red-700' : 'text-gray-900' }}">
                        {{ $jobItem->due_date?->format('d M Y H:i') ?? '—' }}
                        @if($jobItem->isOverdue())
                            (overdue)
                        @elseif($jobItem->due_date?->isToday())
                            (due today)
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Submitted At</div>
                    <div class="text-gray-900">{{ $jobItem->submitted_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        @if($jobItem->isOverdue() || in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_OVERDUE, \App\Models\JobRequestItem::STATUS_CLOSED, \App\Models\JobRequestItem::STATUS_REJECTED], true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Reopen Job</h2>
                <form method="POST" action="{{ route('admin.job-items.reopen', $jobItem) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                        <textarea name="admin_note" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('admin_note') }}</textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">Reopen Job</button>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Project Conversion</h2>

            @if($jobItem->project)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-green-200 bg-green-50 p-4">
                    <div>
                        <div class="font-semibold text-green-900">Converted to Project</div>
                        <div class="text-sm text-green-800">{{ $jobItem->project->project_code }} - {{ $jobItem->project->title }}</div>
                    </div>
                    <a href="{{ route('admin.projects.show', $jobItem->project) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        View Project
                    </a>
                </div>
            @elseif($jobItem->isConvertibleToProject())
                <form method="POST" action="{{ route('admin.job-items.convert-to-project', $jobItem) }}">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                        Convert to Project
                    </button>
                </form>
            @else
                <div class="text-gray-600">Only approved items can be converted to a project.</div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Submission Notes</h2>
            @if($latestAttempt)
                <div class="text-sm text-gray-600 mb-2">
                    Latest submission by {{ $latestAttempt?->user?->name ?? 'field staff' }}
                    on {{ $latestAttempt?->created_at?->format('d M Y H:i') ?? '—' }}
                </div>
                <div class="text-gray-900 whitespace-pre-line">{{ optional($latestAttempt)->notes ?: '—' }}</div>
            @else
                <div class="text-gray-600">No submissions yet.</div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Inspection Photos</h2>
            @if($generalMedia->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($generalMedia as $media)
                        @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            @if($mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 hover:text-blue-900">
                                    <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Inspection photo' }}" class="mb-2 h-32 w-full rounded-lg object-cover">
                                    {{ $media->file_name ?? 'Inspection photo' }}
                                </a>
                            @else
                                <div class="font-semibold text-gray-900">{{ $media->file_name ?? 'Inspection photo' }}</div>
                            @endif
                            <div class="text-xs text-gray-600 mt-1">
                                {{ $media->file_type ?: 'Photo' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-gray-600">No photos uploaded.</div>
            @endif
        </div>

        @if($latestAttempt && $jobItem->checklistItems->count())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Checklist Report</h2>
                <div class="space-y-3">
                    @foreach($jobItem->checklistItems as $checklistItem)
                        @php($itemMedia = $mediaByChecklist->get($checklistItem->id, collect()))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Step {{ $loop->iteration }}</div>
                                    <div class="font-semibold text-gray-900 mt-1">{{ $checklistItem->title }}</div>
                                    @if($checklistItem->description)
                                        <div class="text-sm text-gray-600 mt-1">{{ $checklistItem->description }}</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-700">
                                    {{ str_replace('_', ' ', \Illuminate\Support\Str::title($checklistItem->status)) }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                @if($checklistItem->response)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Response</div>
                                        <div class="text-gray-900">{{ $checklistItem->response }}</div>
                                    </div>
                                @endif
                                @if($checklistItem->notes)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Note</div>
                                        <div class="text-gray-900">{{ $checklistItem->notes }}</div>
                                    </div>
                                @endif
                            </div>

                            @if($itemMedia->count())
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($itemMedia as $media)
                                        @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                                            @if($mediaUrl)
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 hover:text-blue-900">
                                                    <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Checklist photo' }}" class="mb-2 h-32 w-full rounded-lg object-cover">
                                                    {{ $media->file_name ?? 'Checklist photo' }}
                                                </a>
                                            @else
                                                <div class="font-semibold text-gray-900">{{ $media->file_name ?? 'Checklist photo' }}</div>
                                            @endif
                                            <div class="text-xs text-gray-600 mt-1">
                                                {{ $media->file_type ?: 'Photo' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($jobItem->status === \App\Models\JobRequestItem::STATUS_PENDING_ADMIN_REVIEW && $latestAttempt)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Final Review</h2>
                <form method="POST" action="{{ route('admin.job-items.review', $jobItem) }}" class="space-y-4">
                    @csrf
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Approved Requirements</label>
                                <p class="text-xs text-gray-500 mt-1">Edit this list before approving. These rows become the project checklist.</p>
                            </div>
                            <button type="button" id="add-review-requirement" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold text-sm">
                                Add Requirement
                            </button>
                        </div>
                        <div id="review-requirements-list" class="space-y-3">
                            @foreach($reviewRequirementRows as $index => $requirement)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_{{ $index }}_include">Include</label>
                                            <input type="hidden" name="requirements[{{ $index }}][include]" value="0">
                                            <input id="review_requirements_{{ $index }}_include" type="checkbox" name="requirements[{{ $index }}][include]" value="1" @checked(($requirement['include'] ?? '1') === '1') class="h-5 w-5 rounded border-gray-300 text-blue-600">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_{{ $index }}_type">Type</label>
                                            <select id="review_requirements_{{ $index }}_type" name="requirements[{{ $index }}][type]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                                <option value="material" @selected(($requirement['type'] ?? 'material') === 'material')>Material</option>
                                                <option value="task" @selected(($requirement['type'] ?? '') === 'task')>Task</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_{{ $index }}_name">Name</label>
                                            <input id="review_requirements_{{ $index }}_name" type="text" name="requirements[{{ $index }}][name]" value="{{ $requirement['name'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" maxlength="255">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_{{ $index }}_quantity">Quantity</label>
                                            <input id="review_requirements_{{ $index }}_quantity" type="text" name="requirements[{{ $index }}][quantity]" value="{{ $requirement['quantity'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" maxlength="100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_{{ $index }}_notes">Notes</label>
                                            <input id="review_requirements_{{ $index }}_notes" type="text" name="requirements[{{ $index }}][notes]" value="{{ $requirement['notes'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                        <p class="text-xs text-gray-500 mb-2">Required when returning or rejecting a job.</p>
                        <textarea name="admin_note" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('admin_note') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button type="submit" name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold">Approve</button>
                        <button type="submit" name="action" value="return" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2.5 rounded-lg font-semibold">Return</button>
                        <button type="submit" name="action" value="reject" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold">Reject</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Attempt History</h2>

            @if($jobItem->attempts->count() === 0)
                <div class="text-gray-600">No submissions yet.</div>
            @else
                <div class="space-y-4">
                    @foreach($jobItem->attempts as $attempt)
                        @php
                            $attemptStatus = $attempt?->status ?? 'unknown';
                            $attemptLabel = match ($attemptStatus) {
                                \App\Models\JobItemAttempt::STATUS_SUBMITTED => 'Submitted by ' . ($attempt?->user?->name ?? 'field staff'),
                                \App\Models\JobItemAttempt::STATUS_COORDINATOR_APPROVED => 'Approved by Coordinator',
                                \App\Models\JobItemAttempt::STATUS_APPROVED => 'Approved by Admin',
                                \App\Models\JobItemAttempt::STATUS_RETURNED => 'Returned by Admin',
                                \App\Models\JobItemAttempt::STATUS_REJECTED => 'Rejected by Admin',
                                default => str_replace('_', ' ', \Illuminate\Support\Str::title($attemptStatus)),
                            };
                        @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $attemptLabel }}</div>
                                    <div class="text-sm text-gray-600">{{ $attempt?->created_at?->format('d M Y H:i') ?? '—' }}</div>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ match ($attemptStatus) { 'submitted' => 'bg-yellow-100 text-yellow-800', 'coordinator_approved', 'approved' => 'bg-green-100 text-green-800', 'returned' => 'bg-orange-100 text-orange-800', 'rejected' => 'bg-red-100 text-red-800', default => 'bg-gray-200 text-gray-700' } }}">
                                    {{ str_replace('_', ' ', \Illuminate\Support\Str::title($attemptStatus)) }}
                                </span>
                            </div>
                            <div class="mt-3 text-gray-900 whitespace-pre-line">{{ optional($attempt)->notes ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
<script>
    const addReviewRequirementButton = document.getElementById('add-review-requirement');
    const reviewRequirementsList = document.getElementById('review-requirements-list');

    if (addReviewRequirementButton && reviewRequirementsList) {
        addReviewRequirementButton.addEventListener('click', () => {
            const index = reviewRequirementsList.children.length;
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-lg border border-gray-200 bg-gray-50 p-4';
            wrapper.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_${index}_include">Include</label>
                        <input type="hidden" name="requirements[${index}][include]" value="0">
                        <input id="review_requirements_${index}_include" type="checkbox" name="requirements[${index}][include]" value="1" checked class="h-5 w-5 rounded border-gray-300 text-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_${index}_type">Type</label>
                        <select id="review_requirements_${index}_type" name="requirements[${index}][type]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="material">Material</option>
                            <option value="task">Task</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_${index}_name">Name</label>
                        <input id="review_requirements_${index}_name" type="text" name="requirements[${index}][name]" class="w-full border border-gray-300 rounded-lg px-3 py-2" maxlength="255">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_${index}_quantity">Quantity</label>
                        <input id="review_requirements_${index}_quantity" type="text" name="requirements[${index}][quantity]" class="w-full border border-gray-300 rounded-lg px-3 py-2" maxlength="100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1" for="review_requirements_${index}_notes">Notes</label>
                        <input id="review_requirements_${index}_notes" type="text" name="requirements[${index}][notes]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
            `;
            reviewRequirementsList.appendChild(wrapper);
        });
    }
</script>
@endsection
