@extends('layouts.field')

@section('title', 'Job Details | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Job Details</span>
            <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase
                {{ $jobItem->isOverdue() ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}
            </span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 mt-2">{{ $jobItem->jobRequest?->title ?? 'Job Request' }}</h1>
        <p class="text-xs text-slate-500 mt-1">{{ $jobItem->serviceCategory?->name ?? 'Service Category' }}</p>
    </div>

    <!-- Client info panel -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client</span>
            <strong class="block text-slate-800 mt-0.5">{{ $jobItem->jobRequest?->client?->client_name ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Due Date</span>
            <strong class="block mt-0.5 {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                {{ $jobItem->due_date?->format('d M Y H:i') ?? '-' }}
                @if($isOverdue) (overdue) @endif
            </strong>
        </div>
    </div>

    <!-- Job report / submission form -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Job Report</h2>

        @if($isOverdue)
            <div class="p-3.5 bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold rounded-xl leading-relaxed">
                Submission deadline exceeded. Contact coordinator or admin.
            </div>
        @elseif(in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_CLAIMED, \App\Models\JobRequestItem::STATUS_RETURNED], true))
            @if($jobItem->status === \App\Models\JobRequestItem::STATUS_RETURNED)
                <div class="p-3.5 bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-xl space-y-2">
                    <span class="font-bold block">Returned for updates:</span>
                    @if($reviewNote)
                        <div class="bg-white/80 p-2.5 rounded-lg border border-amber-200/20 text-xs italic">
                            "{{ $reviewNote }}"
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('field.jobs.submit', $jobItem) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div class="space-y-1.5">
                    <label for="notes" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Inspection Notes *</label>
                    <textarea id="notes" name="notes" rows="4" required minlength="5" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="Provide details on findings, load calculations, observations..."></textarea>
                </div>

                <!-- Checklist items -->
                <div class="space-y-3">
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Checklist Items</label>
                    
                    @if($checklistItems->isEmpty())
                        <div class="p-3 bg-slate-50 text-slate-500 text-xs rounded-xl border border-slate-100">
                            No default checklist added. Log custom checklist responses below.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($checklistItems as $checklistItem)
                                @php
                                    $checklistTitle = (string) $checklistItem->title;
                                    $checklistGroup = str_contains($checklistTitle, ' - ')
                                        ? \Illuminate\Support\Str::before($checklistTitle, ' - ')
                                        : '';
                                @endphp
                                <div data-checklist-row data-checklist-title="{{ \Illuminate\Support\Str::lower($checklistTitle) }}" data-checklist-group="{{ \Illuminate\Support\Str::lower($checklistGroup) }}" class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 space-y-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <span class="text-[9px] font-extrabold uppercase text-slate-400">
                                                {{ $checklistItem->is_custom ? 'On-site item' : 'Standard check' }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-800 mt-0.5">{{ $checklistItem->title }}</h4>
                                            @if($checklistItem->description)
                                                <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">{{ $checklistItem->description }}</p>
                                            @endif
                                        </div>

                                        <div class="shrink-0">
                                            <select name="checklist[{{ $checklistItem->id }}][status]" data-checklist-status-select class="text-xs border border-slate-200 rounded-lg px-2.5 py-1 bg-white focus:outline-none focus:border-indigo-500">
                                                <option value="pending" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'pending')>Pending</option>
                                                <option value="done" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'done')>Done</option>
                                                <option value="not_applicable" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'not_applicable')>N/A</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                        <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider">Response</label>
                                        
                                        @php
                                            $inputType = $checklistItem->input_type ?? 'textarea';
                                            $options = collect($checklistItem->options ?? []);
                                            $oldResponse = old("checklist.{$checklistItem->id}.response", $checklistItem->response);
                                            $selectedResponses = is_array($oldResponse) ? $oldResponse : array_map('trim', explode(',', (string) $oldResponse));
                                        @endphp

                                        @if($inputType === 'single_choice' && $options->isNotEmpty())
                                            <select name="checklist[{{ $checklistItem->id }}][response]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                                <option value="">-- Select response --</option>
                                                @foreach($options as $option)
                                                    <option value="{{ $option }}" @selected((string) $oldResponse === (string) $option)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($inputType === 'multi_choice' && $options->isNotEmpty())
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                @foreach($options as $option)
                                                    <label class="flex items-center gap-2 bg-white border border-slate-150 p-2.5 rounded-xl text-xs text-slate-700">
                                                        <input type="checkbox" name="checklist[{{ $checklistItem->id }}][response][]" value="{{ $option }}" @checked(in_array((string) $option, $selectedResponses, true)) class="rounded border-slate-350 text-indigo-600 focus:ring-indigo-500">
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($inputType === 'photo')
                                            <input type="file" name="checklist[{{ $checklistItem->id }}][photos][]" multiple accept="image/*" capture="environment" @required($checklistItem->is_required) class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                            <span class="text-[9px] text-slate-400 block mt-1">Capture using camera or upload files. JPG/PNG max 5MB.</span>
                                        @elseif($inputType === 'number')
                                            <input type="number" name="checklist[{{ $checklistItem->id }}][response]" value="{{ $oldResponse }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                        @elseif($inputType === 'text')
                                            <input type="text" name="checklist[{{ $checklistItem->id }}][response]" value="{{ $oldResponse }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                        @elseif($inputType === 'load_table')
                                            @php
                                                $appliances = $options->isNotEmpty()
                                                    ? $options->all()
                                                    : ['Lights','Fans','TV','Refrigerator','Freezer','AC','Water Pump','Computer','Router','Microwave','Washing Machine','Others'];
                                                $savedTable = [];
                                                if (is_string($oldResponse) && str_starts_with(trim($oldResponse), '{')) {
                                                    $decoded = json_decode($oldResponse, true);
                                                    if (is_array($decoded)) $savedTable = $decoded;
                                                } elseif (is_array($oldResponse)) {
                                                    $savedTable = $oldResponse;
                                                }
                                            @endphp
                                            <div class="overflow-x-auto border border-slate-100 rounded-xl mt-1.5 bg-white">
                                                <table class="w-full text-xs border-collapse">
                                                    <thead>
                                                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                                                            <th class="px-3 py-2 text-left font-semibold">Appliance</th>
                                                            <th class="px-2 py-2 text-right font-semibold w-16">Qty</th>
                                                            <th class="px-2 py-2 text-right font-semibold w-20">Watts</th>
                                                            <th class="px-3 py-2 text-right font-semibold w-16">Hrs</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-50">
                                                        @foreach($appliances as $appliance)
                                                            @php $row = $savedTable[$appliance] ?? []; @endphp
                                                            <tr>
                                                                <td class="px-3 py-2 font-medium text-slate-800">{{ $appliance }}</td>
                                                                <td class="px-2 py-1"><input type="number" min="0" name="checklist[{{ $checklistItem->id }}][response][{{ $appliance }}][qty]" value="{{ $row['qty'] ?? '' }}" placeholder="0" class="w-full border border-slate-200 rounded px-1.5 py-0.5 text-right text-xs focus:outline-none"></td>
                                                                <td class="px-2 py-1"><input type="number" min="0" name="checklist[{{ $checklistItem->id }}][response][{{ $appliance }}][power]" value="{{ $row['power'] ?? '' }}" placeholder="0" class="w-full border border-slate-200 rounded px-1.5 py-0.5 text-right text-xs focus:outline-none"></td>
                                                                <td class="px-3 py-1"><input type="number" min="0" step="0.5" name="checklist[{{ $checklistItem->id }}][response][{{ $appliance }}][hours]" value="{{ $row['hours'] ?? '' }}" placeholder="0" class="w-full border border-slate-200 rounded px-1.5 py-0.5 text-right text-xs focus:outline-none"></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <textarea name="checklist[{{ $checklistItem->id }}][response]" rows="3" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">{{ $oldResponse }}</textarea>
                                        @endif
                                    </div>

                                    <div class="space-y-1.5 pt-2">
                                        <label for="checklist_{{ $checklistItem->id }}__notes" class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider">Checklist Item Notes</label>
                                        <input id="checklist_{{ $checklistItem->id }}__notes" type="text" name="checklist[{{ $checklistItem->id }}][notes]" value="{{ old("checklist.{$checklistItem->id}.notes", $checklistItem->notes) }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none" placeholder="Optional comments...">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Custom Checklist Items -->
                <div class="space-y-3.5 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Additional Checklist Items</label>
                        <button type="button" id="add-custom-checklist-button" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-extrabold uppercase">+ Add Item</button>
                    </div>

                    <div id="custom-checklist-list" class="space-y-3">
                        @foreach($customChecklistRows as $index => $item)
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 space-y-3 shadow-xs">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2 sm:col-span-1 space-y-1">
                                        <label for="custom_checklist_{{ $index }}__title" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Item title *</label>
                                        <input id="custom_checklist_{{ $index }}__title" type="text" name="custom_checklist[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. Inverter condition">
                                    </div>
                                    <div class="col-span-2 sm:col-span-1 space-y-1">
                                        <label for="custom_checklist_{{ $index }}__status" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Status</label>
                                        <select id="custom_checklist_{{ $index }}__status" name="custom_checklist[{{ $index }}][status]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                            <option value="pending" @selected(($item['status'] ?? 'pending') === 'pending')>Pending</option>
                                            <option value="done" @selected(($item['status'] ?? '') === 'done')>Done</option>
                                            <option value="not_applicable" @selected(($item['status'] ?? '') === 'not_applicable')>N/A</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label for="custom_checklist_{{ $index }}__notes" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Notes</label>
                                    <input id="custom_checklist_{{ $index }}__notes" type="text" name="custom_checklist[{{ $index }}][notes]" value="{{ $item['notes'] ?? '' }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="Optional comments...">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Material/Task requirements dynamically generated -->
                <div class="space-y-3.5 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Materials &amp; Tasks Requirements</label>
                        <button type="button" id="add-requirement-button" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-extrabold uppercase">+ Add Requirement</button>
                    </div>

                    <div id="requirements-list" class="space-y-3">
                        @foreach($requirementRows as $index => $row)
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 space-y-3 shadow-xs">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label for="requirements_{{ $index }}__type" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Type *</label>
                                        <select id="requirements_{{ $index }}__type" name="requirements[{{ $index }}][type]" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                            <option value="material" @selected(($row['type'] ?? '') === 'material')>Material</option>
                                            <option value="task" @selected(($row['type'] ?? '') === 'task')>Task</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label for="requirements_{{ $index }}__qty" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Quantity</label>
                                        <input id="requirements_{{ $index }}__qty" type="text" name="requirements[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? '' }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. 5, 2 rolls">
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label for="requirements_{{ $index }}__name" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Name *</label>
                                    <input id="requirements_{{ $index }}__name" type="text" name="requirements[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. Cat6 Cable, Mounting Rails">
                                </div>
                                <div class="space-y-1">
                                    <label for="requirements_{{ $index }}__notes" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Notes</label>
                                    <input id="requirements_{{ $index }}__notes" type="text" name="requirements[{{ $index }}][notes]" value="{{ $row['notes'] ?? '' }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="Optional specifications...">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Attachment Files -->
                <div class="space-y-2 pt-4 border-t border-slate-100">
                    <label for="media_files" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">General Report Documents / Photos</label>
                    <input id="media_files" type="file" name="media_files[]" multiple accept="image/*,application/pdf" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                    <span class="text-[9px] text-slate-400 block mt-1">Upload on-site images, calculations, or client signatures. JPG, PNG, PDF max 5MB.</span>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md">
                        Submit Job Report
                    </button>
                </div>
            </form>
        @else
            <!-- Display status summary of currently submitted/approved report -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-600 space-y-3">
                <p>This job report has been submitted and is currently in <strong class="text-slate-800 uppercase text-[10px] bg-slate-200 px-1.5 py-0.5 rounded">{{ $jobItem->status }}</strong> status.</p>
                <div class="border-t border-slate-200/50 pt-2.5">
                    <span class="block text-[9px] font-bold uppercase text-slate-400">Notes submitted</span>
                    <p class="text-slate-700 font-medium mt-1 leading-relaxed">{{ $latestOwnAttempt?->notes ?? 'No notes provided.' }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- History panel showing submitted checklist items & attachments -->
    @if($latestOwnAttempt)
        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-2.5">Submitted Data</h2>

            @if($checklistItems->isNotEmpty())
                <div class="space-y-3 text-xs">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Checklist status</span>
                    <div class="space-y-2">
                        @foreach($checklistItems as $checklistItem)
                            @php
                                $submittedStatus = $checklistItem->status;
                            @endphp
                            <div class="flex items-center justify-between p-2.5 bg-slate-50/50 border border-slate-100 rounded-xl">
                                <div class="min-w-0 pr-4">
                                    <span class="font-bold text-slate-800 block truncate">{{ $checklistItem->title }}</span>
                                    @if($checklistItem->response)
                                        <span class="text-slate-500 block text-[10px] mt-0.5 truncate">Resp: {{ is_array($checklistItem->response) ? implode(', ', $checklistItem->response) : $checklistItem->response }}</span>
                                    @endif
                                </div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase
                                    {{ $submittedStatus === 'done' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $submittedStatus }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($submittedRequirements->isNotEmpty())
                <div class="space-y-3 pt-3 border-t border-slate-50 text-xs">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Requirements summary</span>
                    <div class="space-y-2">
                        @foreach($submittedRequirements as $req)
                            <div class="p-2.5 bg-slate-50/50 border border-slate-100 rounded-xl">
                                <div class="flex justify-between font-bold text-slate-800">
                                    <span>{{ $req->name }}</span>
                                    <span class="text-indigo-600">{{ $req->quantity ?: '1' }}</span>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                    <span class="uppercase font-bold">{{ $req->type }}</span>
                                    <span>{{ $req->notes ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($submittedMedia->isNotEmpty())
                <div class="space-y-3 pt-3 border-t border-slate-50 text-xs">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Attached files</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($submittedMedia as $file)
                            <a href="{{ route('finance.documents.download', $file) }}" class="flex items-center gap-2 p-2 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-100 text-slate-700 min-w-0" title="{{ $file->file_name }}">
                                <span class="text-lg">📄</span>
                                <span class="truncate text-[10px] font-medium">{{ $file->file_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Checklist dependencies and dynamic field JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const addRequirementButton = document.getElementById('add-requirement-button');
        const requirementsList = document.getElementById('requirements-list');
        const addCustomChecklistButton = document.getElementById('add-custom-checklist-button');
        const customChecklistList = document.getElementById('custom-checklist-list');

        const checklistRows = Array.from(document.querySelectorAll('[data-checklist-row]'));

        const setChecklistRowHidden = (row, isHidden) => {
            row.classList.toggle('hidden', isHidden);
            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (isHidden) {
                    field.setAttribute('disabled', 'disabled');
                } else {
                    field.removeAttribute('disabled');
                }
            });
        };

        const selectedChecklistValues = (row) => {
            const values = [];
            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.name?.includes('[status]')) return;

                if (field.type === 'checkbox' && field.checked) {
                    values.push(field.value.toLowerCase().trim());
                } else if (field.tagName === 'SELECT') {
                    values.push(field.value.toLowerCase().trim());
                } else if (field.tagName === 'INPUT' && field.value) {
                    values.push(field.value.toLowerCase().trim());
                }
            });
            return values;
        };

        const rowHasYesNoResponse = (row) => {
            const values = selectedChecklistValues(row);
            return values.includes('yes') || values.includes('no');
        };

        const updateChecklistStatusFromResponse = (row) => {
            const statusSelect = row.querySelector('select[data-checklist-status-select]');
            if (!statusSelect) return;

            const hasMeaningfulResponse = Array.from(row.querySelectorAll('input, select, textarea')).some((field) => {
                if (field.name?.includes('[status]')) return false;

                if (field.type === 'checkbox') return field.checked;
                if (field.type === 'file') return field.files && field.files.length > 0;
                if (field.tagName === 'SELECT' && field.name?.includes('[response]')) return field.value.trim() !== '';

                return field.value && String(field.value).trim() !== '';
            });

            if (hasMeaningfulResponse && statusSelect.value === 'pending') {
                statusSelect.value = 'done';
            }
        };

        const hideChecklistRows = (rows) => {
            rows.forEach((row) => setChecklistRowHidden(row, true));
        };

        const rowsAfterInSameGroup = (triggerRow) => {
            const triggerIndex = checklistRows.indexOf(triggerRow);
            const group = triggerRow.dataset.checklistGroup;
            const dependentRows = [];

            for (let index = triggerIndex + 1; index < checklistRows.length; index += 1) {
                const row = checklistRows[index];
                if (row.dataset.checklistGroup !== group) break;
                if (rowHasYesNoResponse(row)) break;
                dependentRows.push(row);
            }
            return dependentRows;
        };

        const applyChecklistDependencies = () => {
            checklistRows.forEach((row) => setChecklistRowHidden(row, false));

            const installationTypeRow = checklistRows.find((row) => row.dataset.checklistTitle === 'installation type');
            const installationValues = installationTypeRow ? selectedChecklistValues(installationTypeRow) : [];
            const newInstallationOnly = installationValues.includes('new installation')
                && !installationValues.some((value) => value.includes('existing') || value.includes('upgrade') || value.includes('expansion') || value.includes('replacement') || value.includes('maintenance'));

            if (newInstallationOnly) {
                hideChecklistRows(checklistRows.filter((row) => row.dataset.checklistGroup === 'existing system'));
            }

            checklistRows.forEach((row) => {
                const title = row.dataset.checklistTitle || '';
                const values = selectedChecklistValues(row);

                if (!values.includes('no')) return;

                if (title.includes('existing inverter') || title.includes('existing batteries')) {
                    hideChecklistRows(rowsAfterInSameGroup(row));
                }

                if (title.includes('cabling already been installed')) {
                    hideChecklistRows(checklistRows.filter((candidate) => {
                        const candidateTitle = candidate.dataset.checklistTitle || '';
                        return candidateTitle.includes('existing cable type') || candidateTitle.includes('existing cabling condition');
                    }));
                }
            });
        };

        checklistRows.forEach((row) => {
            row.querySelectorAll('input, select, textarea').forEach((field) => {
                field.addEventListener('change', () => {
                    applyChecklistDependencies();
                    updateChecklistStatusFromResponse(row);
                });
            });
            updateChecklistStatusFromResponse(row);
        });

        applyChecklistDependencies();

        if (addRequirementButton && requirementsList) {
            addRequirementButton.addEventListener('click', () => {
                const index = requirementsList.children.length;
                const wrapper = document.createElement('div');
                wrapper.className = "bg-white border border-slate-200 rounded-2xl p-4 space-y-3 shadow-xs";
                wrapper.innerHTML = `
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="requirements_${index}__type" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Type *</label>
                            <select id="requirements_${index}__type" name="requirements[${index}][type]" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                <option value="material">Material</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label for="requirements_${index}__qty" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Quantity</label>
                            <input id="requirements_${index}__qty" type="text" name="requirements[${index}][quantity]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. 5, 2 rolls">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="requirements_${index}__name" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Name *</label>
                        <input id="requirements_${index}__name" type="text" name="requirements[${index}][name]" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. Cat6 Cable, Mounting Rails">
                    </div>
                    <div class="space-y-1">
                        <label for="requirements_${index}__notes" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Notes</label>
                        <input id="requirements_${index}__notes" type="text" name="requirements[${index}][notes]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="Optional specifications...">
                    </div>
                `;
                requirementsList.appendChild(wrapper);
            });
        }

        if (addCustomChecklistButton && customChecklistList) {
            addCustomChecklistButton.addEventListener('click', () => {
                const index = customChecklistList.children.length;
                const wrapper = document.createElement('div');
                wrapper.className = "bg-white border border-slate-200 rounded-2xl p-4 space-y-3 shadow-xs";
                wrapper.innerHTML = `
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2 sm:col-span-1 space-y-1">
                            <label for="custom_checklist_${index}__title" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Item title *</label>
                            <input id="custom_checklist_${index}__title" type="text" name="custom_checklist[${index}][title]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="e.g. Inverter condition">
                        </div>
                        <div class="col-span-2 sm:col-span-1 space-y-1">
                            <label for="custom_checklist_${index}__status" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Status</label>
                            <select id="custom_checklist_${index}__status" name="custom_checklist[${index}][status]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                                <option value="pending">Pending</option>
                                <option value="done">Done</option>
                                <option value="not_applicable">N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="custom_checklist_${index}__notes" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Notes</label>
                        <input id="custom_checklist_${index}__notes" type="text" name="custom_checklist[${index}][notes]" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none" placeholder="Optional comments...">
                    </div>
                `;
                customChecklistList.appendChild(wrapper);
            });
        }
    });
</script>
@endsection
