@extends('admin.layout')

@section('title', 'Job Request Details | ARTSCI Admin Console')

@section('content')
@php
    $canDeleteJobRequest = !$jobRequest->items->contains(fn ($item) => $item->project !== null);
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $jobRequest->title }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $jobRequest->client?->client_name ?? 'Client unavailable' }}</p>
            </div>
            <a href="{{ route('admin.job-requests.index') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Back to Job Requests
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(($errors ?? null)?->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <form method="POST" action="{{ route('admin.job-requests.update', $jobRequest) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                        <input id="job_title" type="text" name="title" value="{{ old('title', $jobRequest->title) }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg font-semibold">
                        Save Title
                    </button>
                </form>

                @if($canDeleteJobRequest)
                    <form method="POST" action="{{ route('admin.job-requests.destroy', $jobRequest) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full items-center justify-center bg-red-50 hover:bg-red-100 text-red-700 px-5 py-2.5 rounded-lg font-semibold" onclick="return confirm('Delete this job and all its category items?')">
                            Delete Job
                        </button>
                    </form>
                @else
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        This job cannot be deleted because it has already been converted to a project.
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Client</div>
                    <div class="text-gray-900 font-semibold">{{ $jobRequest->client?->client_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ in_array($jobRequest->status, ['open', 'reopened'], true) ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-700' }}">
                        {{ str_replace('_', ' ', \Illuminate\Support\Str::title($jobRequest->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                    <div class="text-gray-900">{{ $jobRequest->creator?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Created Date</div>
                    <div class="text-gray-900">{{ $jobRequest->created_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
            <div class="text-gray-900 whitespace-pre-line">{{ $jobRequest->description ?: '—' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <h2 class="text-xl font-bold text-gray-900">Category Items</h2>
                <div class="text-sm text-gray-600">{{ $jobRequest->items->count() }} item{{ $jobRequest->items->count() === 1 ? '' : 's' }}</div>
            </div>

            @if($jobRequest->items->count() === 0)
                <div class="text-gray-600">No category items created for this job.</div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($jobRequest->items as $item)
                        @php
                            $displayStatus = $item->isOverdue() ? \App\Models\JobRequestItem::STATUS_OVERDUE : $item->status;
                            $itemStatusClass = match ($displayStatus) {
                                'overdue' => 'bg-red-100 text-red-800',
                                'closed' => 'bg-gray-300 text-gray-800',
                                'pending_assignment' => 'bg-purple-100 text-purple-800',
                                'open', 'reopened' => 'bg-blue-100 text-blue-800',
                                'claimed' => 'bg-blue-100 text-blue-800',
                                'submitted', 'pending_admin_review' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'returned' => 'bg-orange-100 text-orange-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-200 text-gray-700',
                            };
                        @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $item->serviceCategory?->name ?? $item->title ?? 'Service Category' }}</div>
                                    @if($item->title && $item->serviceCategory && $item->title !== $item->serviceCategory->name)
                                        <div class="text-sm text-gray-600 mt-1">{{ $item->title }}</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold whitespace-nowrap {{ $itemStatusClass }}">
                                    {{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Assigned / Claimed By</div>
                                    <div class="text-gray-900">{{ $item->claimer?->name ?? ($item->status === \App\Models\JobRequestItem::STATUS_PENDING_ASSIGNMENT ? 'Waiting for coordinator' : 'Open for claim') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Due Date</div>
                                    <div class="{{ $item->isOverdue() ? 'font-semibold text-red-700' : 'text-gray-900' }}">
                                        {{ $item->due_date?->format('d M Y H:i') ?? '—' }}
                                        @if($item->isOverdue())
                                            (overdue)
                                        @elseif($item->due_date?->isToday())
                                            (due today)
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="mt-4 rounded-lg border border-gray-200 bg-white p-3 text-sm">
                                 <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Actions</div>
                                 <div class="flex flex-wrap items-center gap-2 mt-2">
                                     <button type="button" onclick="document.getElementById('item-modal-{{ $item->id }}').classList.remove('hidden')" class="inline-flex items-center justify-center bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-md font-semibold text-xs">View Quick Details</button>
                                     <a href="{{ route('admin.job-items.show', $item) }}" class="inline-flex items-center justify-center bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1.5 rounded-md font-semibold text-xs">Open Full Review Page</a>
                                     @if($item->project)
                                         <a href="{{ route('admin.projects.show', $item->project) }}" class="inline-flex items-center justify-center bg-green-100 text-green-800 hover:bg-green-200 px-3 py-1.5 rounded-md font-semibold text-xs">View Project</a>
                                     @elseif($item->isConvertibleToProject())
                                         <form method="POST" action="{{ route('admin.job-items.convert-to-project', $item) }}" class="inline-block">
                                             @csrf
                                             <button type="submit" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md font-semibold text-xs transition">
                                                 @if ($item->status !== \App\Models\JobRequestItem::STATUS_APPROVED && auth()->user()?->isSuperAdmin())
                                                     ⚡ Convert to Project
                                                 @else
                                                     Convert to Project
                                                 @endif
                                             </button>
                                         </form>
                                     @endif
                                     @if($item->isOverdue() || in_array($item->status, ['overdue', 'closed', 'rejected'], true))
                                         <form method="POST" action="{{ route('admin.job-items.reopen', $item) }}" class="inline-block" onsubmit="return confirm('Reopen this overdue job?');">
                                             @csrf
                                             <button type="submit" class="inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-md font-semibold text-xs">Reopen Job</button>
                                         </form>
                                     @endif
                                 </div>
                             </div>

                             <!-- Item Details Popup Modal -->
                             <div id="item-modal-{{ $item->id }}" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) this.classList.add('hidden')">
                                 <div class="bg-white rounded-xl shadow-2xl border border-gray-200 p-6 max-w-2xl w-full relative transform transition-all">
                                     <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                                         <div>
                                             <h3 class="text-lg font-bold text-gray-900">{{ $item->serviceCategory?->name ?? $item->title ?? 'Item Details' }}</h3>
                                             <p class="text-xs text-gray-500">ID #{{ $item->id }} &bull; Job Request: {{ $jobRequest->title }}</p>
                                         </div>
                                         <button type="button" onclick="document.getElementById('item-modal-{{ $item->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1 font-bold text-xl leading-none">&times;</button>
                                     </div>
                                     <div class="space-y-4 text-sm">
                                         <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                             <div>
                                                 <div class="text-xs text-gray-500 uppercase font-semibold">Status</div>
                                                 <div class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $displayStatus) }}</div>
                                             </div>
                                             <div>
                                                 <div class="text-xs text-gray-500 uppercase font-semibold">Claimed By</div>
                                                 <div class="font-semibold text-gray-900">{{ $item->claimer?->name ?? 'Unclaimed' }}</div>
                                             </div>
                                             <div>
                                                 <div class="text-xs text-gray-500 uppercase font-semibold">Due Date</div>
                                                 <div class="{{ $item->isOverdue() ? 'font-bold text-red-700' : 'text-gray-900' }}">{{ $item->due_date?->format('d M Y H:i') ?? '—' }}</div>
                                             </div>
                                             <div>
                                                 <div class="text-xs text-gray-500 uppercase font-semibold">Checklist Count</div>
                                                 <div class="font-semibold text-gray-900">{{ $item->checklistItems->count() }} item(s)</div>
                                             </div>
                                         </div>
                                         @if($item->description)
                                             <div>
                                                 <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Description</div>
                                                 <div class="text-gray-800 bg-white p-3 rounded border border-gray-200 whitespace-pre-line">{{ $item->description }}</div>
                                             </div>
                                         @endif
                                         <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                                             @if($item->isOverdue() || in_array($item->status, ['overdue', 'closed', 'rejected'], true))
                                                 <form method="POST" action="{{ route('admin.job-items.reopen', $item) }}" class="inline-block" onsubmit="return confirm('Reopen this overdue job?');">
                                                     @csrf
                                                     <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-semibold text-xs">Reopen Job</button>
                                                 </form>
                                             @endif
                                             <a href="{{ route('admin.job-items.show', $item) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-xs">Open Full Review Page</a>
                                             <button type="button" onclick="document.getElementById('item-modal-{{ $item->id }}').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold text-xs">Close</button>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-3 text-sm">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Project Conversion</div>
                                @if($item->project)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Converted to Project</span>
                                    <a href="{{ route('admin.projects.show', $item->project) }}" class="block mt-2 font-semibold text-green-700 hover:text-green-900">
                                        {{ $item->project->project_code }} - {{ $item->project->title }}
                                    </a>
                                @elseif($item->status === \App\Models\JobRequestItem::STATUS_APPROVED)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">Not yet converted</span>
                                    <a href="{{ route('admin.job-items.show', $item) }}" class="block mt-2 font-semibold text-blue-700 hover:text-blue-900">
                                        Open item to convert
                                    </a>
                                @else
                                    <div class="text-gray-600">Not yet converted.</div>
                                @endif
                            </div>
                            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-3 text-sm">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Checklist</div>
                                    <div class="text-xs text-gray-500">{{ $item->checklistItems->count() }} item{{ $item->checklistItems->count() === 1 ? '' : 's' }}</div>
                                </div>
                                @if($item->checklistItems->isEmpty())
                                    <div class="text-gray-600">No checklist items yet.</div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($item->checklistItems as $checklistItem)
                                            <div class="rounded border border-gray-200 bg-gray-50 p-2">
                                                <div class="font-semibold text-gray-900">{{ $checklistItem->title }}</div>
                                                @if($checklistItem->description)
                                                    <div class="text-gray-600 mt-1">{{ $checklistItem->description }}</div>
                                                @endif
                                                <form method="POST" action="{{ route('admin.job-items.checklist.destroy', [$item, $checklistItem]) }}" class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-700 font-semibold" onclick="return confirm('Remove this checklist item from this job?')">Remove</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(in_array($item->status, [
                                    \App\Models\JobRequestItem::STATUS_PENDING_ASSIGNMENT,
                                    \App\Models\JobRequestItem::STATUS_OPEN,
                                    \App\Models\JobRequestItem::STATUS_CLAIMED,
                                    \App\Models\JobRequestItem::STATUS_RETURNED,
                                ], true))
                                    <form method="POST" action="{{ route('admin.job-items.checklist.store', $item) }}" class="mt-3 space-y-2">
                                        @csrf
                                        <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Add checklist item">
                                        <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional instruction">
                                        <button type="submit" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-semibold">Add Checklist Item</button>
                                    </form>
                                @endif
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.job-items.show', $item) }}" class="inline-flex items-center justify-center w-full bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">
                                    Review Item
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
