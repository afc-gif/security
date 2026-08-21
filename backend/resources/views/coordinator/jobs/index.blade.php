@extends('layouts.field')

@section('title', 'Job Assignment | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-amber-600 dark:text-amber-450 uppercase tracking-wider">Coordinator Panel</span>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">Job Assignment</h1>
        <p class="text-xs text-slate-500 dark:text-slate-405 mt-1">Assign open field job requests and review submitted report documents.</p>
    </div>

    @if (session('whatsapp_url'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl text-xs text-emerald-805 dark:text-emerald-400 space-y-3" data-whatsapp-redirect="{{ session('whatsapp_url') }}">
            <p class="font-bold flex items-center gap-1.5">
                <span>📱</span> Redirecting to WhatsApp with the admin transport fare notification...
            </p>
            <a class="w-full inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition-all shadow-sm" href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener">
                Open WhatsApp Manually
            </a>
        </div>
    @endif

    {{-- ============================================================
         SECTION 1: REPORTS TO REVIEW (highest priority for coordinator)
         ============================================================ --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Reports to Review</h2>
            @if($submittedJobs->count() > 0)
                <span class="h-5 px-2 bg-rose-500 text-white font-bold text-[10px] rounded-full flex items-center justify-center shrink-0">
                    {{ $submittedJobs->count() }}
                </span>
            @endif
        </div>

        @if($submittedJobs->count() === 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-500 font-semibold shadow-xs">
                No reports are waiting for coordinator review.
            </div>
        @else
            <div class="space-y-4">
                @foreach($submittedJobs as $job)
                    @php
                        $latestAttempt = $job->attempts->first();
                        $mediaByChecklist = $latestAttempt?->media?->whereNotNull('job_checklist_item_id')->groupBy('job_checklist_item_id') ?? collect();
                        $generalMedia = $latestAttempt?->media?->whereNull('job_checklist_item_id') ?? collect();
                        $hasChecklist = $job->checklistItems->count() > 0;
                        $hasRequirements = ($latestAttempt?->requirements->count() ?? 0) > 0;
                        $hasMedia = ($latestAttempt?->media->count() ?? 0) > 0;
                        $collapseId = 'report-detail-' . $job->id;
                        $noteId = 'coordinator_note_' . $job->id;
                    @endphp

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-4 border-l-4 border-l-indigo-650">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $job->jobRequest?->title ?? 'Job Request' }}</h3>
                                <p class="text-[10px] text-slate-405 dark:text-slate-500 font-medium mt-0.5">{{ $job->jobRequest?->client?->client_name ?? 'Client name' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 whitespace-nowrap">
                                Submitted
                            </span>
                        </div>

                        <span class="inline-block px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-350 rounded-lg text-[9px] font-bold">
                            {{ $job->serviceCategory?->name ?? 'Service Category' }}
                        </span>

                        {{-- Report summary strip --}}
                        <div class="bg-slate-50 dark:bg-slate-950/50 rounded-xl p-3 text-xs space-y-2">
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>Field staff:</span>
                                <strong class="text-slate-800 dark:text-slate-200">{{ $job->claimer?->name ?? '—' }}</strong>
                            </div>
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>Submitted:</span>
                                <strong class="text-slate-800 dark:text-slate-200">{{ $job->submitted_at?->format('d M Y H:i') ?? '—' }}</strong>
                            </div>
                            @if($latestAttempt?->notes)
                                <div class="border-t border-slate-150/40 dark:border-slate-850 pt-2 space-y-0.5">
                                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Report Notes</span>
                                    <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed">{{ $latestAttempt->notes }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Collapsible full report detail --}}
                        @if($hasChecklist || $hasRequirements || $hasMedia)
                            <button class="w-full text-left text-xs font-bold text-indigo-650 dark:text-indigo-400 flex items-center gap-1.5 focus:outline-none"
                                    type="button"
                                    aria-expanded="false"
                                    aria-controls="{{ $collapseId }}"
                                    onclick="toggleReportCollapse(this, '{{ $collapseId }}')">
                                <span class="chevron transform transition-transform inline-block">▶</span>
                                <span>View report details ({{ $job->checklistItems->count() }} items)</span>
                            </button>

                            <div id="{{ $collapseId }}" class="hidden space-y-4 pt-2 border-t border-slate-100 dark:border-slate-850">
                                @if($hasChecklist)
                                    <div class="space-y-2">
                                        <strong class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-505 block tracking-wider">Checklist Report</strong>
                                        <div class="space-y-2">
                                            @foreach($job->checklistItems as $checklistItem)
                                                @php($itemMedia = $mediaByChecklist->get($checklistItem->id, collect()))
                                                <div class="p-3 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-850 rounded-xl text-xs space-y-1.5">
                                                    <div class="flex justify-between font-bold text-slate-800 dark:text-white">
                                                        <span>Step {{ $loop->iteration }}: {{ $checklistItem->title }}</span>
                                                        <span class="uppercase text-[9px] px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-350">
                                                            {{ $checklistItem->status }}
                                                        </span>
                                                    </div>
                                                    @if($checklistItem->description)
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-405 leading-relaxed">{{ $checklistItem->description }}</p>
                                                    @endif
                                                    
                                                    @if($checklistItem->response)
                                                        @if($checklistItem->input_type === 'load_table' && str_starts_with(trim($checklistItem->response), '{') && is_array($tbl = json_decode($checklistItem->response, true)))
                                                            <div class="overflow-x-auto border border-slate-100 dark:border-slate-800 rounded-lg bg-white dark:bg-slate-950 mt-1">
                                                                <table class="w-full text-[11px] border-collapse">
                                                                    <thead>
                                                                        <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 font-bold text-slate-500 text-[10px]">
                                                                            <th class="px-2 py-1 text-left">Appliance</th>
                                                                            <th class="px-2 py-1 text-center w-12">Qty</th>
                                                                            <th class="px-2 py-1 text-center w-16">Watts</th>
                                                                            <th class="px-2 py-1 text-center w-12">Hrs</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850 text-slate-700 dark:text-slate-300">
                                                                        @foreach($tbl as $appliance => $row)
                                                                            @if(!empty($row['qty']) || !empty($row['power']) || !empty($row['hours']))
                                                                                <tr>
                                                                                    <td class="px-2 py-1 font-semibold">{{ $appliance }}</td>
                                                                                    <td class="px-2 py-1 text-center">{{ $row['qty'] ?? '—' }}</td>
                                                                                    <td class="px-2 py-1 text-center">{{ $row['power'] ?? '—' }}</td>
                                                                                    <td class="px-2 py-1 text-center">{{ $row['hours'] ?? '—' }}</td>
                                                                                </tr>
                                                                            @endif
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="text-slate-650 dark:text-slate-350"><strong class="font-bold text-slate-500 dark:text-slate-500">Response:</strong> {{ $checklistItem->response }}</p>
                                                        @endif
                                                    @endif

                                                    @if($checklistItem->notes)
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 italic">"{{ $checklistItem->notes }}"</p>
                                                    @endif

                                                    @if($itemMedia->count())
                                                        <div class="grid grid-cols-2 gap-1.5 pt-1">
                                                            @foreach($itemMedia as $media)
                                                                @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                                                <div class="bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800 rounded-lg p-1.5 flex items-center gap-1.5 min-w-0">
                                                                    <span class="text-sm">📷</span>
                                                                    @if($mediaUrl)
                                                                        <a class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 truncate hover:underline" href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                                            {{ $media->file_name ?? 'Photo' }}
                                                                        </a>
                                                                    @else
                                                                        <span class="text-[9px] text-slate-700 dark:text-slate-300 truncate font-semibold">{{ $media->file_name ?? 'Photo' }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($hasRequirements)
                                    <div class="space-y-2">
                                        <strong class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-505 block tracking-wider">Report Requirements</strong>
                                        <div class="space-y-1.5">
                                            @foreach($latestAttempt->requirements as $requirement)
                                                <div class="p-2.5 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-850 rounded-xl text-xs flex items-center justify-between">
                                                    <div>
                                                        <span class="text-[9px] font-extrabold uppercase text-slate-405 dark:text-slate-500 block">{{ $requirement->type }}</span>
                                                        <strong class="text-slate-850 dark:text-white mt-0.5 block">{{ $requirement->name }}</strong>
                                                    </div>
                                                    <span class="text-xs font-bold text-indigo-650 dark:text-indigo-400">{{ $requirement->quantity ?: '1' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($generalMedia->count())
                                    <div class="space-y-2">
                                        <strong class="text-[10px] font-extrabold uppercase text-slate-405 dark:text-slate-500 block tracking-wider">Evidence Files</strong>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($generalMedia as $media)
                                                @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-850 rounded-xl p-2.5 flex items-center gap-2 min-w-0">
                                                    <span class="text-base">📄</span>
                                                    <div class="min-w-0 flex-1 text-[9px]">
                                                        @if($mediaUrl)
                                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="text-indigo-650 dark:text-indigo-400 font-bold truncate block hover:underline">{{ $media->file_name ?? basename($media->file_path) }}</a>
                                                        @else
                                                            <span class="font-bold text-slate-700 dark:text-slate-300 truncate block">{{ $media->file_name ?? basename($media->file_path) }}</span>
                                                        @endif
                                                        <span class="text-slate-400 dark:text-slate-500 block mt-0.5">{{ number_format($media->file_size / 1024, 1) }} KB</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Decision options form --}}
                        <form method="POST" action="{{ route('coordinator.jobs.review', $job) }}" id="review-form-{{ $job->id }}">
                            @csrf
                            <div class="bg-slate-50 dark:bg-slate-950/40 border border-slate-150/40 dark:border-slate-850 rounded-xl p-3 space-y-3">
                                <span class="block text-[10px] font-extrabold uppercase text-slate-450 dark:text-slate-500 tracking-wider">Coordinator Decision</span>

                                <div class="grid grid-cols-2 gap-2">
                                    <button class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-3 rounded-lg transition-colors shadow-sm"
                                            type="submit"
                                            name="action"
                                            value="approve">
                                        ✓ Approve
                                    </button>
                                    <button class="inline-flex items-center justify-center bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-450 border border-amber-200 dark:border-amber-900/50 font-bold text-xs py-2 px-3 rounded-lg transition-colors"
                                            type="submit"
                                            name="action"
                                            value="return"
                                            onclick="return checkDecisionNote(this, '{{ $noteId }}')">
                                        ↩ Return
                                    </button>
                                </div>

                                <div class="space-y-1">
                                    <textarea id="{{ $noteId }}"
                                              name="coordinator_note"
                                              class="w-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500"
                                              rows="2"
                                              placeholder="Decision comments (required for Return)...">{{ old('coordinator_note') }}</textarea>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-505 block leading-normal">Provide adjustment instructions if returning reports to field staff.</span>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            @if($submittedJobs->hasPages())
                <div class="mt-4">
                    {{ $submittedJobs->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ============================================================
         SECTION 2: PENDING ASSIGNMENT
         ============================================================ --}}
    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-850">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Pending Assignment</h2>
            @if($pendingJobs->count() > 0)
                <span class="h-5 px-2 bg-slate-600 text-white font-bold text-[10px] rounded-full flex items-center justify-center shrink-0">
                    {{ $pendingJobs->count() }}
                </span>
            @endif
        </div>

        @if($pendingJobs->count() === 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-500 font-semibold shadow-xs">
                No jobs are waiting for assignment.
            </div>
        @else
            <div class="space-y-4">
                @foreach($pendingJobs as $job)
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $job->jobRequest?->title ?? 'Job Request' }}</h3>
                                <p class="text-[10px] text-slate-405 dark:text-slate-500 font-medium mt-0.5">{{ $job->jobRequest?->client?->client_name ?? 'Client' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-amber-50 dark:bg-amber-955/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50 whitespace-nowrap">
                                Pending
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-350 rounded-lg text-[9px] font-bold">
                                {{ $job->serviceCategory?->name ?? 'Service Category' }}
                            </span>
                        </div>

                        <div class="text-[10px] text-slate-500 dark:text-slate-400">Due: <strong class="text-slate-800 dark:text-slate-200">{{ $job->due_date?->format('d M Y H:i') ?? '—' }}</strong></div>

                        {{-- Checklist management in assign card --}}
                        @if($job->checklistItems->count())
                            <div class="space-y-2 border-t border-slate-50 dark:border-slate-850 pt-3">
                                <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Job Checklist Items</span>
                                <div class="space-y-1.5">
                                    @foreach($job->checklistItems->take(4) as $checklistItem)
                                        <div class="p-2 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-850 rounded-xl text-xs flex items-center justify-between gap-3">
                                            <span class="text-slate-800 dark:text-slate-200 truncate">{{ $checklistItem->title }}</span>
                                            <form method="POST" action="{{ route('coordinator.jobs.checklist.destroy', [$job, $checklistItem]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-[10px] text-rose-600 hover:text-rose-800 font-extrabold uppercase focus:outline-none" type="submit" onclick="return confirm('Remove this checklist item from this job?')">Remove</button>
                                            </form>
                                        </div>
                                    @endforeach
                                    @if($job->checklistItems->count() > 4)
                                        <button type="button" 
                                                onclick="toggleChecklistModal('checklist-modal-{{ $job->id }}', true)"
                                                class="w-full text-center py-2 bg-slate-50 dark:bg-slate-950/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-indigo-650 dark:text-indigo-400 font-bold text-[10px] uppercase rounded-xl border border-dashed border-slate-200 dark:border-slate-800 transition-colors focus:outline-none">
                                            +{{ $job->checklistItems->count() - 4 }} more checklist items (Manage)
                                        </button>

                                        <!-- Checklist Overlay Modal -->
                                        <div id="checklist-modal-{{ $job->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
                                            <div class="bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800 rounded-3xl w-full max-w-sm max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                                                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-850 flex items-center justify-between">
                                                    <div class="min-w-0 pr-4">
                                                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">All Checklist Items</h3>
                                                        <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block truncate">{{ $job->jobRequest?->title ?? 'Job Request' }}</span>
                                                    </div>
                                                    <button type="button" onclick="toggleChecklistModal('checklist-modal-{{ $job->id }}', false)" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-300 font-bold text-lg focus:outline-none shrink-0">&times;</button>
                                                </div>
                                                <div class="flex-1 overflow-y-auto p-5 space-y-2">
                                                    @foreach($job->checklistItems as $checklistItem)
                                                        <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-850 rounded-2xl text-xs flex items-center justify-between gap-3 shadow-xs">
                                                            <div class="min-w-0 flex-1">
                                                                <strong class="text-slate-800 dark:text-slate-200 block truncate">{{ $checklistItem->title }}</strong>
                                                                @if($checklistItem->description)
                                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate block mt-0.5">{{ $checklistItem->description }}</span>
                                                                @endif
                                                            </div>
                                                            <form method="POST" action="{{ route('coordinator.jobs.checklist.destroy', [$job, $checklistItem]) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="text-[10px] text-rose-600 hover:text-rose-800 font-extrabold uppercase focus:outline-none shrink-0" type="submit" onclick="return confirm('Remove this checklist item from this job?')">Remove</button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-950/20 text-center">
                                                    <button type="button" onclick="toggleChecklistModal('checklist-modal-{{ $job->id }}', false)" class="w-full inline-flex items-center justify-center bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs py-2.5 px-4 rounded-xl focus:outline-none">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-2.5 bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-850 text-slate-400 dark:text-slate-500 text-xs rounded-xl text-center">
                                No checklist items added yet.
                            </div>
                        @endif

                        {{-- Quick Checklist Add form --}}
                        <form method="POST" action="{{ route('coordinator.jobs.checklist.store', $job) }}" class="space-y-2.5 pt-2 border-t border-slate-50 dark:border-slate-850">
                            @csrf
                            <span class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Add Checklist Item</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="title" placeholder="Item Title (e.g. Battery condition)" maxlength="255" required class="w-full text-xs border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-2.5 py-1.5 focus:outline-none">
                                <input type="text" name="description" placeholder="Notes / Instruction" class="w-full text-xs border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-2.5 py-1.5 focus:outline-none">
                            </div>
                            <button class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-lg transition-colors focus:outline-none" type="submit">
                                Add Item
                            </button>
                        </form>

                        {{-- Assign to staff form --}}
                        <form method="POST" action="{{ route('coordinator.jobs.assign', $job) }}" class="space-y-2.5 pt-3 border-t border-slate-100 dark:border-slate-850">
                            @csrf
                            <label for="assigned_to_{{ $job->id }}" class="block text-[9px] font-bold text-slate-400 dark:text-slate-505 uppercase tracking-wider">Assign Job to Staff</label>
                            <div class="flex gap-2">
                                <select id="assigned_to_{{ $job->id }}" name="assigned_to" required class="flex-1 text-xs border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-slate-900 dark:text-white rounded-lg px-2.5 py-1.5 focus:outline-none">
                                    <option value="">Select staff</option>
                                    @foreach($fieldStaff as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }}{{ $staff->role === 'field_coordinator' ? ' (Coordinator)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-1.5 rounded-lg transition-all" type="submit">
                                    Assign
                                </button>
                            </div>
                        </form>

                        {{-- Self-claim / release actions --}}
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-50 dark:border-slate-850">
                            <form method="POST" action="{{ route('coordinator.jobs.claim', $job) }}">
                                @csrf
                                <button class="w-full bg-slate-55 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-1.5 px-3 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors" type="submit">
                                    Assign to Me
                                </button>
                            </form>
                            <form method="POST" action="{{ route('coordinator.jobs.release', $job) }}">
                                @csrf
                                <button class="w-full bg-slate-55 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-1.5 px-3 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors" type="submit">
                                    Release to Board
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($pendingJobs->hasPages())
                <div class="mt-4">
                    {{ $pendingJobs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    function toggleReportCollapse(btn, id) {
        const el = document.getElementById(id);
        if (!el) return;
        const isHidden = el.classList.contains('hidden');
        
        el.classList.toggle('hidden', !isHidden);
        btn.setAttribute('aria-expanded', String(isHidden));
        
        const chevron = btn.querySelector('.chevron');
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }

    function toggleChecklistModal(id, show) {
        const modal = document.getElementById(id);
        if (modal) {
            if (show) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent main page scrolling when modal is open
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = ''; // Restore main page scrolling
            }
        }
    }

    function checkDecisionNote(btn, noteId) {
        const textarea = document.getElementById(noteId);
        if (!textarea) return true;
        if (textarea.value.trim() !== '') {
            textarea.classList.remove('border-rose-500');
            return true;
        }
        
        textarea.focus();
        textarea.classList.add('border-rose-500');
        textarea.placeholder = 'A note is required when returning reports to field staff.';
        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Handle WhatsApp automatic redirects
        const whatsappNotice = document.querySelector('[data-whatsapp-redirect]');
        if (whatsappNotice) {
            const whatsappUrl = whatsappNotice.dataset.whatsappRedirect;
            if (whatsappUrl) {
                setTimeout(() => {
                    window.location.href = whatsappUrl;
                }, 800);
            }
        }
    });
</script>
@endsection
