@extends('admin.layout')

@section('title', 'Job Item Review | ARTSCI Admin Console')

@section('content')
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
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ match ($jobItem->status) { 'open', 'reopened' => 'bg-blue-100 text-blue-800', 'claimed' => 'bg-blue-100 text-blue-800', 'submitted' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'returned' => 'bg-orange-100 text-orange-800', 'overdue', 'rejected' => 'bg-red-100 text-red-800', 'closed' => 'bg-gray-300 text-gray-800', default => 'bg-gray-200 text-gray-700' } }}">
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

        @if(in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_OVERDUE, \App\Models\JobRequestItem::STATUS_CLOSED, \App\Models\JobRequestItem::STATUS_REJECTED], true))
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

        @if($jobItem->status === \App\Models\JobRequestItem::STATUS_SUBMITTED && $latestAttempt)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Review</h2>
                <form method="POST" action="{{ route('admin.job-items.review', $jobItem) }}" class="space-y-4">
                    @csrf
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
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ match ($attemptStatus) { 'submitted' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'returned' => 'bg-orange-100 text-orange-800', 'rejected' => 'bg-red-100 text-red-800', default => 'bg-gray-200 text-gray-700' } }}">
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
@endsection
